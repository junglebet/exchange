<script>
import Template from '{Template}/Admin/Pages/Admin/Currencies/Form.template'
import AppLayout from '@/Layouts/AdminLayout'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import TextInput from '@/Jetstream/TextInput'
import TextareaInput from "@/Jetstream/TextareaInput";
import LoadingButton from "@/Jetstream/LoadingButton";
import TrashedMessage from "@/Jetstream/TrashedMessage";
import SelectInput from "@/Jetstream/SelectInput";
import EmptyColumn from "@/Jetstream/EmptyColumn";

const defaultForm = {
    name: null,
    symbol: null,
    type: null,
    networks: [],
    decimals: 0,
    decimals_trc: 0,
    decimals_erc: 0,
    decimals_bep: 0,
    decimals_matic: 0,
    status: true,
    file_id: null,
    deposit_status: true,
    withdraw_status: true,

    // withdraw fee
    withdraw_fee: 0,
    withdraw_fee_fixed: 0,
    withdraw_fee_bep: 0,
    withdraw_fee_erc: 0,
    withdraw_fee_trc: 0,
    withdraw_fee_matic: 0,
    withdraw_fee_bep_fixed: 0,
    withdraw_fee_erc_fixed: 0,
    withdraw_fee_trc_fixed: 0,
    withdraw_fee_matic_fixed: 0,

    deposit_fee: 0,
    deposit_fee_fixed: 0,
    deposit_fee_bep: 0,
    deposit_fee_erc: 0,
    deposit_fee_trc: 0,
    deposit_fee_matic: 0,
    deposit_fee_bep_fixed: 0,
    deposit_fee_erc_fixed: 0,
    deposit_fee_trc_fixed: 0,
    deposit_fee_matic_fixed: 0,

    min_deposit: 0,
    max_deposit: 0,
    min_withdraw: 0,
    max_withdraw: 0,
    min_deposit_confirmation: 0,
    contract: '',
    bep_contract: '',
    trc_contract: '',
    coinpayments_description: '',
    bank_account: null,
    bank_status: null,
    cc_status: null,
    cc_exchange_rate: null,
    txn_explorer: null,
};

export default Template({
    components: {
        NavButtonLink,
        AppLayout,
        TextInput,
        TextareaInput,
        LoadingButton,
        TrashedMessage,
        SelectInput,
        EmptyColumn
    },
    props: {
        isMarkets: {
            type: Boolean,
            default: false,
        },
        isEdit: {
            type: Boolean,
            default: false,
        },
        errors: Object,
        currency: Object,
        settings: Object,
        networks: Array,
        networkIds: Array,
        bankAccounts: Array,
    },
    remember: 'form',
    data() {
        return {
            sending: false,
            form: Object.assign({}, defaultForm),
            coinpaymentCoins: {},
            coinpaymentSelected: null,
            currencyLogo: null,
            uploaded: false,
            uploadUrl: this.route('file-upload'),
            uploadHeaders: {
                'X-XSRF-TOKEN' : $cookies.get('XSRF-TOKEN')
            }
        }
    },
    mounted() {
        if(this.isEdit) {
            this.form = this.currency;
            this.form.networks = this.networkIds;
            this.coinpaymentSelected = this.form.alt_symbol;

            if(this.form.file) {
                this.currencyLogo = {
                    name: this.form.file.name,
                    type: this.form.file.type,
                    url: this.form.file.url
                }
            }
        }
        this.loadCurrenciesApi();
    },
    computed: {
        subTitle: function () {
            return this.isEdit ? this.currency.name : 'Create';
        },
        actionButtonTitle: function () {
            return this.isEdit ? 'Update Currency' : 'Create Currency';
        },
    },
    methods: {
        submit() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => this.sending = false,
                onSuccess: () => {
                    this.$toast.open('Database updated');
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            if(this.form.networks.includes(1)) {
                this.form.decimals = 8;
                this.form.alt_symbol = this.coinpaymentSelected;
            } else {
                this.form.alt_symbol = null;
            }

            if(this.form.symbol) {
                this.form.symbol = this.form.symbol.toUpperCase();
            }

            if(this.form.type == 'coin') {
                this.form.bank_account = null;
                this.form.bank_status = null;
                this.form.cc_status = null;
                this.form.cc_exchange_rate = null;
            } else {
                this.form.min_deposit_confirmation = 0;
                this.form.contract = null;
                this.form.bep_contract = null;
            }

            if(this.isEdit) {
                this.$inertia.put(this.route('admin.currencies.update', this.currency.id), this.form, afterRequest);
            } else {
                this.$inertia.post(this.route('admin.currencies.store'), this.form, afterRequest);
            }
        },
        destroy() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (this.isMarkets) {
                alert("You can not delete this currency. This currency was paired in markets");
                return;
            }

            if (confirm('Are you sure you want to delete this currency?')) {
                this.$inertia.delete(this.route('admin.currencies.destroy', this.currency.id), {
                    onSuccess: () => { this.$toast.open('Currency was deleted'); }
                })
            }
        },
        restore() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (confirm('Are you sure you want to restore this currency?')) {
                this.$inertia.put(this.route('admin.currencies.restore', this.currency.id))
            }
        },
        loadCurrenciesApi() {
            axios.get(this.route('admin.currencies.coinpayments.coins')).then((res) => {
                this.coinpaymentCoins = res.data;
            });
        },
        removePic: function(){
            let currencyLogo = this.currencyLogo;
            this.$refs.fileUploadForm.deleteUpload(this.uploadUrl, this.uploadHeaders, [currencyLogo], {
                uuid: this.form.file_id,
            });
            this.currencyLogo = null;
            this.form.file_id = null;
            this.uploaded = false;
        },
        upload: function(){
            let self = this;
            this.$refs.fileUploadForm.upload(this.uploadUrl, this.uploadHeaders, [this.currencyLogo]).then(function(){
                self.uploaded = true;
                setTimeout(function(){
                    // self.currencyLogo.progress(0);
                }, 500);
            });
        },
        onSelect: function(fileRecords){
            this.upload();
            this.uploaded = false;
        },
        onUpload: function(responses){
            let response = responses[0];
            if (!response.error) {
                this.form.file_id = response.data.uuid;
            }
        },
    },
    watch: {
        'form.type': function (type, newType) {
            if(newType) {
                this.form.networks = [];
            }
        },
        'form.networks': function (type, newType) {
            if(this.form.networks.includes(1)) {
                this.form.decimals = 8;
            }
        },
    }
});
</script>
