<script>
import Template from '{Template}/Web/Pages/Market/Partials/MarketTrades.template'
import {math_formatter} from "@/Functions/Math";

export default Template({
    components: {
    },
    props: {
        market: Object,
    },
    data() {
        return {
            limit: 30,
            fetchInterval: null,
        }
    },
    mounted() {
        if(this.market.s) {
            this.fetchHistoricalTrades();
        } else {
            this.$store.dispatch('fetchMarketTrades', { market: this.market.name, route: this.route('markets.api.trades') });
        }
    },
    beforeDestroy() {
        clearInterval(this.fetchInterval);
    },
    computed: {
        trades: function () {
            return _.take(this.$store.getters.getMarketTrades(this.market.name), this.limit);
        },
    },
    methods: {
        fetchHistoricalTrades() {
            axios.get(this.route('markets.api.historical.trades'), {
                params: {
                    market: this.market.name
                }
            }).then((response) => {
                if(response.data.success && response.data.trades) {
                    response.data.trades.forEach((trade) => {
                        this.$store.dispatch('updateMarketTrade', {
                            market: this.market,
                            trade: trade
                        });
                    });
                }
            })
        },
        //markets.api.historical.trades
        decimal_format(value, decimal, type = '') {

            let formatted = math_formatter(value, decimal);

            if(type == "fiat") {
                if(decimal == 3) {
                    formatted = numeral(formatted).format('0,0.000');
                } else {
                    formatted = numeral(formatted).format('0,0.00');
                }
            }

            return formatted;
        },
        parseTime(date) {
            return moment(date).format('HH:mm:ss');
        },

    },
})
</script>
