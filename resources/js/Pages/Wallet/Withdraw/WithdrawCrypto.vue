<script>
import Template from '{Template}/Web/Pages/Wallet/Withdraw/WithdrawCrypto.template'
import AppLayout from '@/Layouts/AppLayout'
import TextUserInput from "@/Jetstream/TextUserInput";
import TextUserInputBadge from "@/Jetstream/TextUserInputBadge";
import SelectInput from "@/Jetstream/SelectInput";
import {mapGetters} from "vuex";
import {string_cut} from "@/Functions/String";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import LoadingButton from "@/Jetstream/LoadingButton";
import SvgIcon from "@/Components/Svg/SvgIcon";
import {math_formatter, math_percentage} from "@/Functions/Math";
import { router } from '@inertiajs/vue2'

const defaultForm = {
    network: "",
    address: null,
    amount: 0,
    payment_id: null,
};

export default Template({
    components: {
        AppLayout,
        TextUserInput,
        TextUserInputBadge,
        SelectInput,
        JetDialogModal,
        JetSecondaryButton,
        LoadingButton,
        SvgIcon
    },
    props: {
        symbol: String,
        currency: Object,
        errors: Object,
        limit: Object,
        currencies: Array,
    },
    mounted() {

        if(this.currency) {
            this.activeAsset = this.currency;
            this.loadNetworks();
        }

        if(_.isEmpty(this.wallets)) {
            this.$store.dispatch('fetchWallets', this.route('wallets.index'));
        }

        if(_.isEmpty(this.withdrawals)) {
            this.fetchWithdrawals();
        }

        this.fetchInterval = setInterval(() => {
            this.fetchWithdrawals();
        }, 10000);
    },
    data() {
        return {
            state: 0,
            activeNetwork: null,
            networks: {},
            activeAsset: {},
            form: Object.assign({}, defaultForm),
            showWithdrawalModal: false,
            closeWithdrawalModal: false,
            withdrawalModal: null,
            sending: false,
            fetchInterval: null,
        }
    },
    beforeDestroy() {
        clearInterval(this.fetchInterval);
    },
    computed: {
        ...mapGetters({
            wallets: 'getWallets',
            withdrawals: 'getWithdrawals',
        }),
        wallet: function () {
            if(this.$store.getters.getUser) {
                return this.$store.getters.getWallet(this.currency.symbol);
            }
        },
        withdrawFee: function() {
            let fee = this.currency.withdraw_fee;
            let feeFixed = this.currency.withdraw_fee_fixed;

            if(this.activeNetwork == 3) {
                fee = this.currency.withdraw_fee_erc;
                feeFixed = this.currency.withdraw_fee_erc_fixed;
            }

            if(this.activeNetwork == 6) {
                fee = this.currency.withdraw_fee_bep;
                feeFixed = this.currency.withdraw_fee_bep_fixed;
            }

            if(this.activeNetwork == 8) {
                fee = this.currency.withdraw_fee_trc;
                feeFixed = this.currency.withdraw_fee_trc_fixed;
            }

            if(this.activeNetwork == 16) {
                fee = this.currency.withdraw_fee_matic;
                feeFixed = this.currency.withdraw_fee_matic_fixed;
            }

            if(parseFloat(fee) > 0) {
                return {type: 'floating', fee: math_formatter(fee, 2)};
            }

            return {type: 'fixed', fee: feeFixed};
        },
        calculatedFee: function () {

            if(!this.form.amount) return 0;

            let withdrawFee = this.withdrawFee;

            if(withdrawFee.type == "fixed") {
                return math_formatter(this.form.amount - withdrawFee.fee, 8);
            }

            return math_formatter(this.form.amount - math_percentage(this.form.amount, withdrawFee.fee), 8);
        },
    },
    methods: {
        changeAsset() {
            if(this.state > 0) {
                return router.visit(this.route('wallets.withdraw.crypto', this.activeAsset.symbol));
            }
            this.state++;
        },
        loadNetworks() {
            axios.get(this.route('wallets.api.deposit.networks'), {
                params: {
                    'symbol' : this.activeAsset.symbol
                }
            }).then((response) => {
                this.networks = response.data;
                this.activeNetwork = null;
            }).catch(error => {

            });
        },
        fetchWithdrawals() {
            this.$store.dispatch('fetchWithdrawals', this.route('wallets.api.withdrawals', {
                type: 'coin'
            }));
        },
        format_string(string, limit) {
            return string_cut(string, limit);
        },
        doCopy(string) {
            this.$copyText(string).then(() => {
                this.$toast.open(this.$t('Text was copied to the clipboard'));
            }, function (e) {

            })
        },
        withdraw() {

            if(this.sending) return;

            this.sending = true;

            this.form.network = this.activeNetwork;
            this.form.symbol = this.currency.symbol;

            axios.post(this.route('wallets.api.withdraw'), this.form).then((response) => {
                this.$inertia.visit(this.route('wallets.withdraw.crypto.success'));
            }).catch(error => {
                this.sending = false;
                _.each(error.response.data.errors, (field, key) => {
                    this.$toast.error(field[0]);
                });
            });
        },
        openModal(withdrawal) {
            this.withdrawalModal = withdrawal;
            this.showWithdrawalModal = true;
        },
        closeModal() {
            this.showWithdrawalModal = false;
        },
        handleInput ($event) {

            let keyCode = ($event.keyCode ? $event.keyCode : $event.which);

            if ((keyCode < 48 || keyCode > 57) && (keyCode !== 46 || this.form.amount.toString().indexOf('.') != -1)) {
                $event.preventDefault();
            }

            let precision = 8;

            // restrict to 2 decimal places
            if(this.form.amount != null && this.form.amount.toString().indexOf(".")>-1 && (this.form.amount.toString().split('.')[1].length >= precision)){
                $event.preventDefault();
            }
        },
        clearInput ($event) {
            if(this.form.amount.toString().charAt(0) == '.') {
                this.form.amount = 0;
            }
        },
        setMaxAmount() {
            this.form.amount = this.wallet.balance_in_wallet;
        }
    },
})
</script>
