<script>
import Template from '{Template}/Admin/Pages/Admin/Reports/FiatWithdrawals.template'
import AppLayout from '@/Layouts/AdminLayout'
import Welcome from '@/Jetstream/Welcome'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import Pagination from '@/Jetstream/Pagination'
import NavLink from "@/Jetstream/NavLink";
import Badge from "@/Jetstream/Badge";
import SearchFilter from '@/Jetstream/SearchFilter'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import {string_cut} from "@/Functions/String";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetDangerButton from '@/Jetstream/DangerButton'
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
            selectedAction: null,
            selectedWithdrawal: null,
            withdrawalBeingReviewed: false,
            withdrawalBankInfoReviewed: false,
            bankInfo: null,
            showReasonModal: false,
            rejectedReason: null,
            rejectedReasonText: "",
            noteText: "",
            sending: false,
            form: {
                search: this.filters.search,
            },
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
            this.$inertia.replace(this.route('admin.reports.withdrawals.fiat', Object.keys(query).length ? query : { remember: 'forget' }))
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

                    this.noteText = '';
                    this.rejectedReasonText = '';
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            let form = {
                'action': this.selectedAction
            };

            if(this.selectedAction == "reject") {

                if(this.rejectedReasonText.trim() == "") {
                    alert("Please fill the rejection reason field");
                    return false;
                }

                form.reason = this.rejectedReasonText.trim();
            }

            if(this.selectedAction == "approve") {
                form.note = this.noteText.trim();
            }

            this.$inertia.put(this.route('admin.reports.withdrawals.fiat.moderate', this.selectedWithdrawal.id), form, afterRequest);
        },
        showReason(withdrawal) {
            this.showReasonModal = true;
            this.rejectedReason = withdrawal.rejected_reason;
        },
        showBankInfo(withdrawal) {
            this.bankInfo = withdrawal;
            this.withdrawalBankInfoReviewed = true;
        },
        closeReasonModal() {
            this.showReasonModal = false;
        },
        closeBankInfoModal() {
            this.withdrawalBankInfoReviewed = false;
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
