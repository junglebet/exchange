<script>
import Template from '{Template}/Web/Layout/AppBasic.template'
import JetApplicationMark from '@/Jetstream/ApplicationMark'
import JetBanner from '@/Jetstream/Banner'
import JetDropdown from '@/Jetstream/Dropdown'
import JetDropdownLink from '@/Jetstream/DropdownLink'
import JetNavLink from '@/Jetstream/NavLink'
import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink'
import Socket from "@/Jetstream/Socket";
import ThemeMode from "@/Components/ThemeMode";
import BottomMenu from "@/Components/BottomMenu";
import SidebarMenu from "@/Components/SidebarMenu";

export default Template({
    components: {
        Socket,
        JetApplicationMark,
        JetBanner,
        JetDropdown,
        JetDropdownLink,
        JetNavLink,
        JetResponsiveNavLink,
        ThemeMode,
        BottomMenu,
        SidebarMenu
    },

    computed: {
        logo() {
            return this.$page.props.siteLogo
        }
    },
    mounted() {
        // Store user in vuex store
        this.setUser();

        if(!this.$page.props.alt) {
            window.TyperSetup();
        }
    },
    methods: {
        setUser() {
            if(this.$page.props.user && !this.$store.getters.getUser) {
                this.$store.dispatch('setUser', {user: this.$page.props.user});
            }
        },
        isUrl(urls) {
            let currentUrl = this.$page.url.substr(1).split('/');
            if(currentUrl[0]) {
                return currentUrl[0].startsWith(urls)
            }
        },
    },
})
</script>
