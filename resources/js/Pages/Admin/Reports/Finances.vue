<script>
import Template from '{Template}/Admin/Pages/Admin/Reports/Finances.template'
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
import {math_formatter, math_percentage} from "@/Functions/Math";

export default Template({
    components: {
        Badge,
        NavLink,
        NavButtonLink,
        AppLayout,
        Welcome,
        Pagination,
        SearchFilter,
        AdminReportsTab
    },
    props: {
        filters: Object,
    },
    data() {
        return {
            reports: null,
            types: [
                {'id': 'trades', 'name': 'Trades'},
                {'id': 'deposits', 'name': 'Deposits'},
                {'id': 'withdrawals', 'name': 'Withdrawals'},
                {'id': 'fiat_deposits', 'name': 'Fiat Deposits'},
                {'id': 'fiat_withdrawals', 'name': 'Fiat Withdrawals'},
            ],
            sending: false,
            form: {
                period: null,
                type: null
            },
        }
    },
    mounted() {
        //this.fetchReport()
    },
    methods: {
        math_formatter(value, decimals) {
            return math_formatter(value, decimals);
        },
        format_string(string, limit) {
            return string_cut(string, limit);
        },
        doCopy(string) {
            this.$copyText(string).then(() => {
                this.$toast.open('Text was copied to the clipboard');
            }, function (e) {

            })
        },
        fetchReport () {

            axios.get(this.route('admin.reports.finances.fetch'), {
                params: {
                    type: this.form.type,
                    period: this.form.period
                }
            }).then((response) => {
                this.reports = response.data[this.form.type];
            });
        }
    },
    watch: {
        form: {
            handler: throttle(function() {
                if(this.form.period && this.form.type) {
                    this.fetchReport()
                }
            }, 150),
            deep: true,
        },
    },
})
</script>
