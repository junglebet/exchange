<script>
import Template from '{Template}/Web/Pages/MarketLite/Markets.template'
import AppLayout from '@/Layouts/AppLayout'
import TableFilter from "@/Mixins/Filter/TableFilter";
import IconFilter from "@/Components/Table/IconFilter";
import MarketMixin from "@/Mixins/Market/MarketMixin";

export default Template({
    components: {
        AppLayout,
        IconFilter
    },
    mixins: [MarketMixin, TableFilter],
    beforeDestroy () {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.onResize, { passive: true })
        }
    },
    computed: {
        markets: function () {

            let markets = _.map(this.$store.getters.getMarkets, (market) => {
                market['favorite'] = this.isFavorite(market.name);
                return market;
            });

            let orderMarkets = _.orderBy(markets, (market) => {

                if(this.filter.filterBy == 'name') {
                    return market[this.filter.filterBy];
                } else {
                    return parseFloat(market[this.filter.filterBy]);
                }

            }, this.filter.filterDirection == 'desc' ? 'desc' : 'asc');

            return _.filter(orderMarkets, (market) => {
                let sortedBy = true;

                if(this.filter.sortBy == "favorites") {
                    sortedBy = market.favorite == true;
                } else if(this.filter.sortBy == "futures") {
                    sortedBy = market.has_futures == true;
                } else if(this.filter.sortBy) {
                    sortedBy = market.quote_currency == this.filter.sortBy;
                }

                return market.name.toLowerCase().includes(this.filter.search.toLowerCase()) && sortedBy;
            });
        },
    },
    mounted() {

        this.loadFavorites();

        this.setFilter('last', 'desc', true);

        if(_.isEmpty(this.markets)) {
            this.$store.dispatch('fetchMarkets', this.route('markets.api.ticker'));
        }

        this.onResize();
        window.addEventListener('resize', this.onResize, { passive: true })

    },
    methods: {
        filterName(name) {

            if(this.filter.sortBy == "futures") {
                name = name.replace('-', '').replace('_', '').replace(':', '')
            }

            return name;
        },
        onResize () {
            if(window.innerWidth >= 986) {
                this.$inertia.visit(this.route('markets'));
            } else {
                //this.$inertia.visit(this.route('market.lite', window.globalMarket.name));
            }
        }
    }
})
</script>
