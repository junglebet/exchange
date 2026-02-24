<script>
import JetButton from '@/Jetstream/Button'
import JetFormSection from '@/Jetstream/FormSection'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetLabel from '@/Jetstream/Label'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import Template from '{Template}/Web/Pages/Profile/BankAccounts.template'
import SvgIcon from "@/Components/Svg/SvgIcon";
import {math_formatter} from "@/Functions/Math";

import Badge from "@/Jetstream/Badge";
import TextUserInput from "@/Jetstream/TextUserInput";

export default Template({
    components: {
        JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        JetSecondaryButton,
        SvgIcon,
        Badge,
        TextUserInput
    },
    data() {
        return {
            depositResponse: '',
            depositState: false,
            account: null,
            accounts: null,
            sending: false,
            deleting: false,
            form: this.$inertia.form({
                first_name: '',
                last_name: '',
                phone_number: '',
                date_of_birth: '',
                ssn: '',
                city: '',
                street: '',
                postal: '',
                state: '',
            }),
            depositSending: false,
            depositForm: {
                account: '',
                amount: 0,
                type: '',
            },
            transactions: null
        }
    },
    mounted() {

        this.getAccounts();
        this.getTransactions();

        if(typeof window.Plaid === "undefined")
        {
            let themejs = document.createElement('script');
            themejs.setAttribute('src', 'https://cdn.plaid.com/link/v2/stable/link-initialize.js')
            document.head.appendChild(themejs);
        }
    },
    methods: {
        parseTime(date) {
            return moment(date).format('YYYY-MM-DD HH:ss');
        },
        decimal_format(value, decimal) {
            return math_formatter(value, decimal);
        },
        submitKyc() {

            if(this.form.processing) return;

            this.form.post(this.route('bank_account.kyc_submit'), {
                errorBag: 'bankAccountKyc',
                preserveScroll: true
            });
        },
        acceptNumber() {
            var x = this.form.phone_number.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            this.form.phone_number = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        },
        launchPlaid() {

            if(this.sending) return false;

            this.sending = true;

            axios.post(this.route('bank_account.plaid_token')).then((response) => {

                this.sending = false;

                const handler = Plaid.create({
                    token: response.data.token,
                    onSuccess: (public_token, metadata) => {

                        this.account = {
                          'account_id': metadata.account.id,
                          'name': metadata.account.name,
                          'token': public_token,
                        };

                        this.linkAccount();
                    },
                    onLoad: () => { },
                    onExit: (err, metadata) => {

                    },
                    onEvent: (eventName, metadata) => { },
                    receivedRedirectUri: null,
                });

                handler.open();

            }).catch(error => {
                this.sending = false;
            });
        },
        linkAccount() {
            axios.post(this.route('bank_account.link_account'), this.account).then(() => {
                this.getAccounts();
            });
        },
        async getAccounts() {
            await axios.get(this.route('bank_account.accounts')).then((response) => {
                this.accounts = response.data.accounts;
            }).catch(error => {
                this.accounts = null;
            });
        },
        async getTransactions() {
            await axios.get(this.route('bank_account.transactions')).then((response) => {
                this.transactions = response.data.transactions;
            }).catch(error => {
                this.transactions = null;
            });
        },
        deleteAccount(account) {

            if(this.deleting) return;

            if (confirm('Are you sure you want to delete this linked bank account?')) {

                this.deleting = true;

                axios.post(this.route('bank_account.delete_account'), {
                    'name': account.accountName
                }).then(() => {
                    this.deleting = false;
                    this.getAccounts();
                }).catch(error => {
                    this.deleting = false;
                    this.accounts = null;
                });

            }
        },
        deposit(account, type) {
            this.depositState = true;
            this.depositForm.account = account.accountName;
            this.depositForm.type = type;
        },
        depositSubmit() {

            this.depositResponse = '';

            if(this.depositSending) return;

            this.depositSending = true;

            axios.post(this.route('bank_account.deposit'), this.depositForm).then((response) => {
                this.depositSending = false;
                this.depositResponse = response.data.message;
                this.depositForm.amount = '';
                this.getTransactions();
            }).catch(error => {
                this.depositSending = false;
                this.depositResponse = error.response.data.errors.amount[0];
                this.depositForm.amount = '';
            });
        },
        handleInput ($event) {

            let keyCode = ($event.keyCode ? $event.keyCode : $event.which);

            if ((keyCode < 48 || keyCode > 57) && (keyCode !== 46 || this.depositForm.amount.toString().indexOf('.') != -1)) {
                $event.preventDefault();
            }

            let precision = 2;

            // restrict to 2 decimal places
            if(this.depositForm.amount != null && this.depositForm.amount.toString().indexOf(".")>-1 && (this.depositForm.amount.toString().split('.')[1].length >= precision)){
                $event.preventDefault();
            }
        },
        clearInput ($event) {
            if(this.depositForm.amount.toString().charAt(0) == '.') {
                this.depositForm.amount = 0;
            }
        },
    },
});
</script>
