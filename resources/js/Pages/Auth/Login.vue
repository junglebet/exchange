<script>
    import Template from '{Template}/Web/Pages/Auth/Login.template';
    import JetAuthenticationCard from '@/Jetstream/AuthenticationCard'
    import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo'
    import JetButton from '@/Jetstream/Button'
    import JetInput from '@/Jetstream/Input'
    import JetCheckbox from '@/Jetstream/Checkbox'
    import JetLabel from '@/Jetstream/Label'
    import JetValidationErrors from '@/Jetstream/ValidationErrors'
    import AppBasicLayout from '@/Layouts/AppBasicLayout'
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
            canResetPassword: Boolean,
            status: String
        },

        data() {
            return {
                form: this.$inertia.form({
                    email: '',
                    password: '',
                    remember: false,
                    'g-recaptcha-response': false,
                })
            }
        },
        mounted() {
            if(this.$page.props.mode == "readonly") {
                this.form.email = 'admin@cex.exchange';
                this.form.password = '8866CEX';
            }
        },
        methods: {
            submit() {
                this.form
                    .transform(data => ({
                        ... data,
                        remember: this.form.remember ? 'on' : ''
                    }))
                    .post(this.route('login'), {
                        onFinish: () => {
                            this.form.reset('password')
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
