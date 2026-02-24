<script>
import Template from '{Template}/Web/Pages/Wallet/Wallets.template'
import AppLayout from '@/Layouts/AppLayout'
import JetCheckbox from '@/Jetstream/Checkbox'
import TableFilter from "@/Mixins/Filter/TableFilter";
import IconFilter from "@/Components/Table/IconFilter";

export default Template({
    components: {
        AppLayout,
        JetCheckbox,
        TableFilter,
        IconFilter
    },
    data() {
        return {
            fetchBalanceInterval : null,
            totalBalance: null,
        }
    },
    mixins: [TableFilter],
    beforeDestroy () {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.onResize, { passive: true })
        }

        clearInterval(this.fetchBalanceInterval)
    },
    mounted() {

        this.setFilter('balance_in_wallet', 'desc', true);

        if(_.isEmpty(this.wallets)) {
            this.$store.dispatch('fetchWallets', this.route('wallets.index'));
        }
        this.onResize();
        window.addEventListener('resize', this.onResize, { passive: true })

        this.fetchTotalBalance();

        this.fetchBalanceInterval = setInterval(() => {
            this.fetchTotalBalance();
        }, 10000);
    },
    computed: {
        wallets: function () {

            let wallets = _.orderBy(this.$store.getters.getWallets, (wallet) => {

                if(this.filter.filterBy == 'symbol' || this.filter.filterBy == 'type') {
                    return wallet[this.filter.filterBy];
                } else {
                    return parseFloat(wallet[this.filter.filterBy]);
                }

            }, this.filter.filterDirection == 'desc' ? 'desc' : 'asc');

            return _.filter(wallets, (wallet) => {
                let sortedBy = true;

                if(this.filter.sortBy == "balance") {
                    sortedBy = parseFloat(wallet.balance_in_wallet) > 0;
                }

                return ((wallet.symbol.toLowerCase().includes(this.filter.search.toLowerCase()) ||
                    wallet.currency.toLowerCase().includes(this.filter.search.toLowerCase()))
                    && sortedBy);
            });


        },
        pricesInUsd: function () {
            return this.$store.getters.getWallet(this.currency.symbol);
        },
    },
    methods: {
        onResize () {

        },
        fetchTotalBalance() {
            axios.get(this.route('currencies.api.rates')).then((response) => {
                this.totalBalance = response.data;
            })
        }
    }
})
</script>
