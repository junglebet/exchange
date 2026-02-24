<script>
import Template from '{Template}/Web/Components/ThemeMode.template'
export default Template({
    data() {
        return {
            isDarkTheme: false
        }
    },
    mounted() {
        this.isDarkTheme = this.$page.props.theme == 'dark' ? true : false;
    },
    methods: {
        setThemeMode(mode) {

            let chartExist = document.getElementById('market-chart');

            if(chartExist) {
                document.getElementById('market-chart').className = 'invisible'
            }

            if(mode == "dark") {
                document.body.classList.add('dark');
                this.isDarkTheme = true;

            } else {
                document.body.classList.remove('dark');
                this.isDarkTheme = false;
            }

            this.$toast.open(this.$t('The theme mode was switched'));
            this.$worker.$emit("themeChanged", mode);

            axios.get(this.route('settings.theme.mode'), {
                params: {
                    mode: mode
                }
            }).then((response) => {

                if(chartExist) {

                    document.getElementById('market-chart').contentWindow.location.reload();

                    setTimeout(() => {
                        document.getElementById('market-chart').className = '';
                    }, 1500);
                }
            }).catch(error => {

            });
        }
    }
})
</script>
