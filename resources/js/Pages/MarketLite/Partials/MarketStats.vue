<script>
import Template from '{Template}/Web/Pages/Market/Partials/MarketStats.template'
import MarketsWidget from "@/Pages/Market/Partials/MarketsWidget";
import {math_formatter} from "@/Functions/Math";
import vClickOutside from 'v-click-outside'

export default Template({
    components: {
        MarketsWidget,
    },
    props: {
        market: Object,
        quotes: [Array, Object],
        futures: Boolean
    },
    computed: {
        market_stats: function () {
            return this.$store.getters.getMarket(this.market.name) ?? this.market;
        },
    },
    data() {
        return {
            marketWidgetVisible: false,
        }
    },
    directives: {
        clickOutside: vClickOutside.directive
    },
    methods: {
        onClickOutside (event) {
            if(event.target.className !== "market-label") {
                this.marketWidgetVisible = false;
            }
        },
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
        toggleMarketWidget() {
            this.marketWidgetVisible = !this.marketWidgetVisible;
        }
    }
});
</script>
