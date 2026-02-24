<script>
import Template from '{Template}/Web/Components/Reports/ReportsTab.template'
export default Template({
    props: ['section'],
    data() {
        return {
            tabPage: '',
        }
    },
    mounted() {
        if(this.section == "trades")
            this.tabPage = 'reports.trades';
        if(this.section == "deposits")
            this.tabPage = 'reports.deposits';
        if(this.section == "withdrawals")
            this.tabPage = 'reports.withdrawals';
        if(this.section == "fiat_deposits")
            this.tabPage = 'reports.deposits.fiat';
        if(this.section == "fiat_withdrawals")
            this.tabPage = 'reports.withdrawals.fiat';
        if(this.section == "referrals")
            this.tabPage = 'reports.referral-transactions';
    },
    methods: {
        setReport(report) {
            this.$inertia.visit(this.route(report));
        },
        setTabPage() {
            this.$inertia.visit(this.route(this.tabPage));
        }
    }
})
</script>
