<script>
import SelectInput from "@/Jetstream/SelectInput";
import Template from '{Template}/Web/Pages/Wallet/Deposit/Fiat/DepositCc.template'
import AppLayout from '@/Layouts/AppLayout'
import {string_cut} from "@/Functions/String";
import JetButton from '@/Jetstream/Button'
import LoadingButton from "@/Jetstream/LoadingButton";
import TextUserInput from "@/Jetstream/TextUserInput";

export default Template({
    components: {
        AppLayout,
        SelectInput,
        JetButton,
        LoadingButton,
        TextUserInput
    },
    props: {
        symbol: String,
        currency: Object,
        errors: Object,
        stripe: String
    },
    mounted() {
        if (typeof Stripe === 'undefined') {
            this.loadStripe();
        } else {
            this.stripeInstance = Stripe(this.stripe);
        }
    },
    data() {
        return {
            step: 'amount',
            amount: 0,
            method: 'cc',
            wallet : false,
            walletData: false,
            card: null,
            secret: null,
            stripeInstance: null,
            sending: false,
        }
    },
    methods: {
        loadStripe() {
            this.$loadScript("https://js.stripe.com/v3")
                .then(() => {
                    this.stripeInstance = Stripe(this.stripe);
                })
                .catch((error) => {

                });
        },
        getStripeToken() {

            if(this.sending) return false;

            this.sending = true;

            let purchase = {
                currency_id: this.currency.id,
                amount: this.amount,
            };

            axios.post(this.route('wallets.api.deposit.fiat.stripe.load'), purchase).then((response) => {

                this.step = 'payment';

                setTimeout(() => {
                    this.sending = false;

                    this.secret = response.data.clientSecret;

                    let elements = this.stripeInstance.elements();

                    let style = {
                        base: {
                            color: "#32325d",
                            fontFamily: 'Arial, sans-serif',
                            fontSmoothing: "antialiased",
                            fontSize: "16px",
                            "::placeholder": {
                                color: "#32325d"
                            }
                        },
                        invalid: {
                            fontFamily: 'Arial, sans-serif',
                            color: "#fa755a",
                            iconColor: "#fa755a"
                        }
                    };

                    this.card = elements.create("card", { style: style });

                    this.card.mount("#card-element");

                }, 10);

            }).catch(error => {
                this.sending = false;
                this.step = 'amount';
                _.each(error.response.data.errors, (field, key) => {
                    this.$toast.error(field[0]);
                });
            });
        },
        format_string(string, limit) {
            return string_cut(string, limit);
        },
        submit() {

            if(this.step == "payment") return this.purchase();

            this.getStripeToken();
        },
        purchase() {

            if(this.sending) return;

            this.sending = true;

            this.stripeInstance
                .confirmCardPayment(this.secret, {
                    payment_method: {
                        card: this.card
                    }
                })
                .then((result) => {
                    this.sending = false;

                    if (result.error) {
                        this.$toast.error(result.error.message);
                    } else {
                        this.$toast.open(this.$t('Your payment is being processed'));
                        this.$inertia.visit(this.route('wallets.deposit.fiat.success'));
                    }
                }).catch(error => {
                    this.sending = false;
                });
        },
        handleInput ($event) {

            let keyCode = ($event.keyCode ? $event.keyCode : $event.which);

            if ((keyCode < 48 || keyCode > 57) && (keyCode !== 46 || this.amount.toString().indexOf('.') != -1)) {
                $event.preventDefault();
            }

            // restrict to 2 decimal places
            if(this.amount.toString() != null && this.amount.toString().indexOf(".")>-1 && (this.amount.toString().split('.')[1].length > 8)){
                $event.preventDefault();
            }
        },
        clearInput ($event) {
            if(this.amount.toString().charAt(0) == '.') {
                this.amount = 0;
            }
        },
    }
})
</script>
