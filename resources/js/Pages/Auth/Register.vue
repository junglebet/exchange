<script>
    import AppBasicLayout from '@/Layouts/AppBasicLayout'
    import Template from '{Template}/Web/Pages/Auth/Register.template';
    import JetAuthenticationCard from '@/Jetstream/AuthenticationCard'
    import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo'
    import JetButton from '@/Jetstream/Button'
    import JetInput from '@/Jetstream/Input'
    import JetCheckbox from "@/Jetstream/Checkbox";
    import JetLabel from '@/Jetstream/Label'
    import JetValidationErrors from '@/Jetstream/ValidationErrors'
    import VueRecaptcha from 'vue-recaptcha';

    export default Template({
        components: {
            AppBasicLayout,
            JetAuthenticationCard,
            JetAuthenticationCardLogo,
            JetButton,
            JetInput,
            JetCheckbox,
            JetLabel,
            JetValidationErrors,
            VueRecaptcha
        },
        props: {
            errors: Object,
        },
        data() {
            return {
                referralState: false,
                form: this.$inertia.form({
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    terms: false,
                    referral: '',
                    'g-recaptcha-response': false,
                })
            }
        },
        mounted() {
            if(this.$page.props.referral_code) {
                this.referralState = true;
                this.form.referral = this.$page.props.referral_code;
            }
        },
        methods: {
            submit() {
                this.form.post(this.route('register'), {
                    onFinish: () => {
                        this.form.reset('password', 'password_confirmation')
                        this.$refs.recaptcha.reset()
                    },
                })
            },
            onVerify: function (response) {
                Object.defineProperty(this.form, 'g-recaptcha-response', {
                    value: response
                });
            },
        }
    });
</script>
