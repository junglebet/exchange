<script>
    import AppLayout from '@/Layouts/AppLayout'
    import DeleteUserForm from './DeleteUserForm'
    import JetSectionBorder from '@/Jetstream/SectionBorder'
    import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm'
    import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm'
    import UpdatePasswordForm from './UpdatePasswordForm'
    import UpdateProfileInformationForm from './UpdateProfileInformationForm'
    import ProfileInformation from "./ProfileInformation";
    import BankAccounts from "./BankAccounts";
    import Vouchers from "./Vouchers";
    import Template from '{Template}/Web/Pages/Profile/Show.template'

    export default Template({
        props: ['sessions', 'slug'],
        data() {
            return {
                tabPage: '',
                page : 'info',
            }
        },
        components: {
            AppLayout,
            DeleteUserForm,
            JetSectionBorder,
            LogoutOtherBrowserSessionsForm,
            TwoFactorAuthenticationForm,
            UpdatePasswordForm,
            UpdateProfileInformationForm,
            ProfileInformation,
            BankAccounts,
            Vouchers
        },
        mounted() {

            let slug = this.slug;

            if(!this.slug) {
                slug = this.page;
            }

            this.page = slug;
            this.tabPage = slug;

        },
        methods: {
            setPage(page, external = false) {

                if(external) {
                    return this.$inertia.visit(this.route(page));
                }

                this.$inertia.visit(this.route('profile.show', page));
            },
            setTabPage() {

                if(this.tabPage == "api_tokens") {
                    this.$inertia.visit(this.route('api-tokens.index'));

                } else if(this.tabPage == "kyc") {
                    this.$inertia.visit(this.route(this.tabPage));
                } else {
                    this.$inertia.visit(this.route('profile.show', this.tabPage));
                }
            }
        }
    });
</script>
