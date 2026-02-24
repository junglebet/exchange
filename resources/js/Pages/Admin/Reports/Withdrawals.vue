<script>
import Template from '{Template}/Admin/Pages/Admin/Reports/Withdrawals.template'
import AppLayout from '@/Layouts/AdminLayout'
import Welcome from '@/Jetstream/Welcome'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import Pagination from '@/Jetstream/Pagination'
import NavLink from "@/Jetstream/NavLink";
import Badge from "@/Jetstream/Badge";
import SearchFilter from '@/Jetstream/SearchFilter'
import JetDialogModal from '@/Jetstream/DialogModal'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetDangerButton from '@/Jetstream/DangerButton'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import {string_cut} from "@/Functions/String";
import AdminReportsTab from "@/Components/Reports/AdminReportsTab";

export default Template({
    components: {
        Badge,
        NavLink,
        NavButtonLink,
        AppLayout,
        Welcome,
        Pagination,
        SearchFilter,
        JetDialogModal,
        JetSecondaryButton,
        JetConfirmationModal,
        JetDangerButton,
        AdminReportsTab
    },
    props: {
        withdrawals: Object,
        filters: Object,
    },
    data() {
        return {
            withdrawalBeingReviewed: false,
            selectedAction: null,
            selectedWithdrawal: null,
            sending: false,
            form: {
                search: this.filters.search,
                type: this.filters.type,
            },
            showReasonModal: false,
            rejectedReason: null,
            rejectedReasonText: ""
        }
    },
    methods: {
        format_string(string, limit) {
            return string_cut(string, limit);
        },
        doCopy(string) {
            this.$copyText(string).then(() => {
                this.$toast.open('Text was copied to the clipboard');
            }, function (e) {

            })
        },
        reset() {
            this.form = mapValues(this.form, () => null)
        },
        getList() {
            let query = pickBy(this.form)
            this.$inertia.replace(this.route('admin.reports.withdrawals', Object.keys(query).length ? query : { remember: 'forget' }))
        },
        approve(withdrawal) {
            this.selectedWithdrawal = withdrawal;
            this.selectedAction = 'approve';
            this.withdrawalBeingReviewed = true;
        },
        reject(withdrawal) {
            this.selectedWithdrawal = withdrawal;
            this.selectedAction = 'reject';
            this.withdrawalBeingReviewed = true;
        },
        refresh() {
            this.getList();
            this.$toast.success(this.$t('Withdrawal states are refreshed'))
        },
        confirmAction() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => this.sending = false,
                onSuccess: () => {
                    this.withdrawalBeingReviewed = false;
                    this.$toast.open('Withdrawal was moderated');
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            let form = {
                'action': this.selectedAction,
            };

            let actionRoute = this.route('admin.reports.withdrawals.moderate', this.selectedWithdrawal.id);

            if(this.selectedAction == "reject") {

                if(this.rejectedReasonText.trim() == "") {
                    alert("Please fill the rejection reason field");
                    return false;
                }

                form.reason = this.rejectedReasonText;
            } else {

                if(this.selectedWithdrawal.network_id == 17) {

                    let brcData = JSON.parse(this.selectedWithdrawal.raw);

                    if (typeof window.unisat == 'undefined') {
                        this.$toast.error('Please install Unisat Wallet to sign this BRC20 withdrawal.');
                    }

                    let accounts = unisat.requestAccounts();

                    accounts.then((accounts) => {

                        unisat.sendInscription(this.selectedWithdrawal.address, brcData.inscriptionId).then((tx) => {
                            console.log(tx);
                            if(tx) {
                                form.txn = tx;
                                this.$inertia.put(actionRoute, form, afterRequest);
                            }
                        });
                    });

                    return;
                }
            }

            this.$inertia.put(actionRoute, form, afterRequest);
        },
        showReason(document) {
            this.showReasonModal = true;
            this.rejectedReason = document.rejected_reason;
        },
        closeReasonModal() {
            this.showReasonModal = false;
        }
    },
    watch: {
        form: {
            handler: throttle(function() {
                this.getList()
            }, 150),
            deep: true,
        },
    },
})
</script>
