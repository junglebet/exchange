<script>
import SelectInput from "@/Jetstream/SelectInput";
import Template from '{Template}/Web/Pages/Wallet/Deposit/DepositCrypto.template'
import AppLayout from '@/Layouts/AppLayout'
import {mapGetters} from "vuex";
import {string_cut} from "@/Functions/String";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import SvgIcon from "@/Components/Svg/SvgIcon";
import QrCode from 'vue-qrcode-component'
import {router} from "@inertiajs/vue2";

export default Template({
    components: {
        AppLayout,
        SelectInput,
        JetDialogModal,
        JetSecondaryButton,
        SvgIcon,
        QrCode,
    },
    props: {
        symbol: String,
        currency: Object,
        errors: Object,
        currencies: Array,
    },
    data() {
        return {
            state: 0,
            networks: {},
            activeAsset: {},
            fetchInterval: null,
            wallet : false,
            walletData: false,
            showDepositModal: false,
            closeDepositModal: false,
            depositModal: null,
            showQr: false,
            showQrAlt: false,
            tooltipOptions: {
                placement: 'auto-start'
            },
            activeNetwork: "",
        }
    },
    mounted() {

        if(this.currency) {
            this.activeAsset = this.currency;
            this.loadNetworks();
        }

        if(_.isEmpty(this.wallets)) {
            this.$store.dispatch('fetchWallets', this.route('wallets.index'));
        }

        if(_.isEmpty(this.deposits)) {;
            this.fetchDeposits();
        }

        this.fetchInterval = setInterval(() => {
            this.fetchDeposits();
        }, 10000);

        //this.loadAddress();
    },
    beforeDestroy() {
        clearInterval(this.fetchInterval);
    },
    computed: {
        ...mapGetters({
            wallets: 'getWallets',
            deposits: 'getDeposits',
        }),
    },
    methods: {
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
        changeAsset() {
            if(this.state > 0) {
                return router.visit(this.route('wallets.deposit.crypto', this.activeAsset.symbol));
            }
            this.state++;
        },
        fetchDeposits() {
            this.$store.dispatch('fetchDeposits', this.route('wallets.api.deposits', {
                type: 'coin'
            }));
        },
        doCopy(string) {
            this.$copyText(string).then(() => {
                this.$toast.open(this.$t('Text was copied to the clipboard'));
            }, function (e) {

            })
        },
        format_string(string, limit) {
            return string_cut(string, limit);
        },
        loadAddress() {

            if(!this.activeNetwork) {
                this.walletData = null;
                return;
            }

            this.getAddress(this.activeNetwork);
        },
        getAddress(network) {

            axios.get(this.route('wallets.api.getAddress'), {
                params: {
                    'network': network,
                    'symbol' : this.activeAsset.symbol
                }
            }).then((response) => {
                this.walletData = response.data;
            }).catch(error => {

            });
        },
        openModal(deposit) {
            this.depositModal = deposit;
            this.showDepositModal = true;
        },
        closeModal() {
            this.showDepositModal = false;
        }
    }
})
</script>
