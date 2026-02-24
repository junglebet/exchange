<script>
import Template from '{Template}/Web/Components/BottomMenu.template'
export default Template({
    data() {
        return {
            profileVisible: false,
        }
    },
    computed: {
        profileMenuOpened: function () {
            return this.$store.getters.getProfileState;
        },
    },
    methods: {
        isUrl(urls) {
            let currentUrl = this.$page.url.substr(1).split('/');
            if(currentUrl[0]) {
                return currentUrl[0].startsWith(urls)
            }
        },
        setPage(page) {

            if(page == "profile") {
                this.$store.dispatch('setProfileState', true);
            } else {
                this.$inertia.visit(this.route(page));

                setTimeout(() => {
                    this.$store.dispatch('setProfileState', false);
                }, 1000);
            }

        }
    }
})
</script>
