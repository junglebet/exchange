<script>
import Template from '{Template}/Admin/Pages/Admin/Settings/Index.template'
import AppLayout from '@/Layouts/AdminLayout'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import TextInput from '@/Jetstream/TextInput'
import TextareaInput from "@/Jetstream/TextareaInput";
import LoadingButton from "@/Jetstream/LoadingButton";
import TrashedMessage from "@/Jetstream/TrashedMessage";
import SelectInput from "@/Jetstream/SelectInput";
import EmptyColumn from "@/Jetstream/EmptyColumn";
import Badge from "@/Jetstream/Badge";

export default Template({
    components: {
        NavButtonLink,
        AppLayout,
        TextInput,
        TextareaInput,
        LoadingButton,
        TrashedMessage,
        SelectInput,
        EmptyColumn,
        Badge
    },
    props: {
        isEdit: {
            type: Boolean,
            default: false,
        },
        errors: Object,
        general: Object,
        trade: Object,
        mail: Object,
        coinpayments: Object,
        ethereum: Object,
        bnb: Object,
        bitcoin: Object,
        polygon: Object,
        tron: Object,
        recaptcha: Object,
        stripe: Object,
        notification: Object,
        social: Object
    },
    data() {
        return {
            validationErrors: {},
            form: {},
            sending: false,
            uploadHeaders: {
                'X-XSRF-TOKEN' : $cookies.get('XSRF-TOKEN')
            },
            uploadUrl: this.route('file-upload'),
            siteLogo: null,
            uploaded: false,
            section: 'general',
            syncingCoins: false,
            coinpaymentsCurrencies: [],
        }
    },
    computed: {
        subTitle: function () {
            return 'Settings';
        },
        actionButtonTitle: function () {
            return 'Update Settings';
        },
    },
    mounted() {
        if(this.general.logo) {
            this.siteLogo = {
                name: '',
                type: 'image',
                url: this.general.logo
            }
        }
    },
    methods: {
        submit(key) {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => {
                    this.sending = false,
                    this.validationErrors = this.errors;
                },
                onSuccess: () => {
                    this.$toast.open('Settings were updated');
                    this.validationErrors = this.errors;
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            if(key == 'general') {
                this.form = { general: this.general };
            }

            if(key == 'trade') {
                this.form = { trade: this.trade };
            }

            if(key == 'mail') {
                this.form = { mail: this.mail };
            }

            if(key == 'coinpayments') {
                this.form = { coinpayments: this.coinpayments };
            }

            if(key == 'ethereum') {
                this.form = { ethereum: this.ethereum };
            }

            if(key == 'bnb') {
                this.form = { bnb: this.bnb };
            }

            if(key == 'polygon') {
                this.form = { polygon: this.polygon };
            }

            if(key == 'tron') {
                this.form = { tron: this.tron };
            }

            if(key == 'bitcoin') {
                this.form = { bitcoin: this.bitcoin };
            }

            if(key == 'recaptcha') {
                this.form = { recaptcha: this.recaptcha };
            }

            if(key == 'stripe') {
                this.form = { stripe: this.stripe };
            }

            if(key == 'notification') {
                this.form = { notification: this.notification };
            }

            if(key == 'social') {
                this.form = { social: this.social };
            }

            this.$inertia.put(this.route('admin.settings.update'), this.form, afterRequest);
        },
        removePic: function(){
            this.general.logo = null;
            this.siteLogo = null;
            this.uploaded = false;
        },
        upload: function(){
            let self = this;
            this.$refs.fileUploadForm.upload(this.uploadUrl, this.uploadHeaders, [this.siteLogo]).then(function(){
                self.uploaded = true;
            });
        },
        onSelect: function(fileRecords){
            this.upload();
            this.uploaded = false;
        },
        onUpload: function(responses){
            let response = responses[0];
            if (!response.error) {
                this.general.logo = response.data.path;
            }
        },
        setSection(section) {
            this.section = section;
            this.validationErrors = [];

            if(section == "coinpayments") {
                this.loadCoins();
            }
        },
        loadCoins(force = false) {

            if(this.coinpaymentsCurrencies.length && !force) return;

            axios.get(this.route('admin.currencies.coinpayments.coins')).then((res) => {
                this.coinpaymentsCurrencies = res.data;
            })
        },
        syncCoins() {

            if(this.syncingCoins) {
                this.$toast.open('Sync is still in progress');
                return;
            } else {
                this.$toast.open('Sync process started');
            }
            this.syncingCoins = true;
            axios.get(this.route('admin.currencies.coinpayments.sync')).then((res) => {
                this.syncingCoins = false;
                this.$toast.open('Coins are synced.');
                this.loadCoins(true);
            })
        }
    },
});
</script>
