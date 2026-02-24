<script>
import Template from '{Template}/Admin/Pages/Admin/Reports/Wallets.template'
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
import AdminReportsTab from "@/Components/Reports/AdminReportsTab";
import TextInput from '@/Jetstream/TextInput'
import JetDialogModal from '@/Jetstream/DialogModal'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetSuccessButton from '@/Jetstream/SuccessButton'


export default Template({
    components: {
        Badge,
        NavLink,
        NavButtonLink,
        AppLayout,
        Welcome,
        TextInput,
        Pagination,
        SearchFilter,
        AdminReportsTab,
        JetDialogModal,
        JetSecondaryButton,
        JetSuccessButton
    },
    props: {
        wallets: Object,
        filters: Object,
    },
    data() {
        return {
            selectedWallet: null,
            sending: false,
            form: {
                search: this.filters.search,
                type: this.filters.type,
                user: this.filters.user
            },
            deposit: '',
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
            this.$inertia.replace(this.route('admin.reports.wallets', Object.keys(query).length ? query : { remember: 'forget' }))
        },
        fetchUser (q) {
            return axios.get(this.route('admin.users.fetch'), {
                params: {
                    q: q
                }
            }).then((response) => {
                return { results: response.data.users};
            });
        },
        transfer(wallet) {
            this.selectedWallet = wallet;
        },
        transferConfirm() {

            if(parseFloat(this.deposit) <= 0 || this.deposit.trim() == "") {
                this.$toast.error('Deposit amount must be greater than 0');
                return;
            }

            axios.post(this.route('admin.reports.wallets.fund'), {
                'amount': this.deposit,
                'wallet': this.selectedWallet.id,
            }).then((response) => {
                this.$toast.open('Deposit of ' + this.deposit + ' ' + this.selectedWallet.currency.symbol +' has been funded successfully.');
                this.selectedWallet = null;
                this.deposit = 0;
                this.getList();
            });
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
