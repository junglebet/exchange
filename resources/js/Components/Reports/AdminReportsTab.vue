<script>
import Template from '{Template}/Admin/Components/Reports/AdminReportsTab.template'

export default Template({
    props: ['section', 'referrer'],
    methods: {
        setReport(report) {

            let form = {};

            if(this.referrer) {
                form = {'referrer': this.referrer};
            }

            this.$inertia.visit(this.route(report, form));
        }
    }
})
</script>
