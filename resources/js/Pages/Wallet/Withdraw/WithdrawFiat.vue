<script>
import Template from '{Template}/Web/Pages/Wallet/Withdraw/WithdrawFiat.template'
import SelectInput from "@/Jetstream/SelectInput";
import AppLayout from '@/Layouts/AppLayout'
import {mapGetters} from "vuex";
import {string_cut} from "@/Functions/String";
import JetButton from '@/Jetstream/Button'
import TextUserInput from "@/Jetstream/TextUserInput";
import TextUserInputBadge from "@/Jetstream/TextUserInputBadge";
import LoadingButton from "@/Jetstream/LoadingButton";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import SvgIcon from "@/Components/Svg/SvgIcon";
import {math_formatter, math_percentage} from "@/Functions/Math";

const defaultForm = {
    currency: null,
    amount: 0,
    name: '',
    iban: '',
    swift: '',
    ifsc: '',
    address: '',
    account_holder_name: '',
    account_holder_address: '',
    country_id: null,
};

export default Template({
    components: {
        AppLayout,
        SelectInput,
        JetButton,
        TextUserInput,
        TextUserInputBadge,
        LoadingButton,
        JetDialogModal,
        JetSecondaryButton,
        SvgIcon
    },
    props: {
        symbol: String,
        currency: Object,
        errors: Object,
        countries: Object,
        limit: Object,
        networks: Array,
    },
    data() {
        return {
            form: Object.assign({}, defaultForm),
            sending: false,
            showWithdrawalModal: false,
            closeWithdrawalModal: false,
            withdrawalModal: null,
        }
    },
    mounted() {

        if(_.isEmpty(this.wallets)) {
            this.$store.dispatch('fetchWallets', this.route('wallets.index'));
        }

        if(_.isEmpty(this.withdrawals)) {
            this.$store.dispatch('fetchFiatWithdrawals', this.route('wallets.api.withdrawals.fiat'));
        }
    },
    computed: {
        ...mapGetters({
            wallets: 'getWallets',
            withdrawals: 'getFiatWithdrawals',
        }),
        wallet: function () {
            if(this.$store.getters.getUser) {
                return this.$store.getters.getWallet(this.currency.symbol);
            }
        },
        withdrawFee: function () {

            let fee = this.currency.withdraw_fee;
            let feeFixed = this.currency.withdraw_fee_fixed;

            if(parseFloat(fee) > 0) {
                return {type: 'floating', fee: math_formatter(fee, 2)};
            }

            return {type: 'fixed', fee: math_formatter(feeFixed, 2)};
        },
        calculatedFee: function () {

            if(!this.form.amount) return 0;

            let withdrawFee = this.withdrawFee;

            if(withdrawFee.type == "fixed") {
                return math_formatter(this.form.amount - withdrawFee.fee, 2);
            }

            return math_formatter(this.form.amount - math_percentage(this.form.amount, withdrawFee.fee), 2);
        },
    },
    methods: {
        format_string(string, limit) {
            return string_cut(string, limit);
        },
        submit() {

            if(this.sending) return;

            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => this.sending = false,
                onSuccess: () => {
                    this.$toast.open('Withdraw was submitted');
                    this.$inertia.visit(this.route('wallets.withdraw.fiat.success'));
                },
                onError: () => {
                    this.sending = false;
                    this.$toast.error('There are some form errors');
                },
            };

            this.form.currency_id = this.currency.id;

            this.$inertia.post(this.route('wallets.withdraw.store.fiat'), this.form, afterRequest);
        },
        openModal(withdrawal) {
            this.withdrawalModal = withdrawal;
            this.showWithdrawalModal = true;
        },
        amountWithFee(withdrawal) {
            return numeral(withdrawal.amount).subtract(numeral(withdrawal.fee).value()).value();
        },
        closeModal() {
            this.showWithdrawalModal = false;
        },
        handleInput ($event) {

            let keyCode = ($event.keyCode ? $event.keyCode : $event.which);

            if ((keyCode < 48 || keyCode > 57) && (keyCode !== 46 || this.form.amount.toString().indexOf('.') != -1)) {
                $event.preventDefault();
            }

            let precision = 3;

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
    }
});
</script>
