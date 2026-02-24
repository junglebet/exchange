<script>
import Template from '{Template}/Admin/Pages/Admin/Reports/FiatDeposits.template'
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
        deposits: Object,
        filters: Object,
    },
    data() {
        return {
            receipt: null,
            selectedAction: null,
            selectedDeposit: null,
            depositBeingReviewed: false,
            showReceipt: false,
            showReasonModal: false,
            rejectedReason: null,
            rejectedReasonText: "",
            sending: false,
            form: {
                search: this.filters.search,
                type: this.filters.type,
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
            this.$inertia.replace(this.route('admin.reports.deposits.fiat', Object.keys(query).length ? query : { remember: 'forget' }))
        },
        showModal(receipt) {
            this.receipt = receipt;
            this.showReceipt = true;
        },
        closeModal() {
            this.showReceipt = false;
        },
        approve(deposit) {
            this.selectedDeposit = deposit;
            this.selectedAction = 'approve';
            this.depositBeingReviewed = true;
        },
        reject(deposit) {
            this.selectedDeposit = deposit;
            this.selectedAction = 'reject';
            this.depositBeingReviewed = true;
        },
        confirmAction() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => this.sending = false,
                onSuccess: () => {
                    this.depositBeingReviewed = false;
                    this.$toast.open('Deposit was moderated');
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

                form.reason = this.rejectedReasonText;
            }

            this.$inertia.put(this.route('admin.reports.deposits.fiat.moderate', this.selectedDeposit.id), form, afterRequest);
        },
        showReason(deposit) {
            this.showReasonModal = true;
            this.rejectedReason = deposit.rejected_reason;
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
