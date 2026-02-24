<script>
import Template from '{Template}/Web/Pages/Market/Market.template'
import AppLayout from '@/Layouts/AppLayout'
import OrderBook from "@/Pages/Market/Partials/OrderBook";
import TextInput from "@/Jetstream/TextInput";
import TButton from "@/Jetstream/Button";
import MarketStats from "@/Pages/Market/Partials/MarketStats";
import OrderForm from "@/Pages/Market/Partials/OrderForm";
import OpenOrders from "@/Pages/Market/Partials/OpenOrders";
import MarketTrades from "@/Pages/Market/Partials/MarketTrades";
import OrderbookChannel from "@/Store/Channels/Public/Market/OrderbookChannel";
import MarketMixin from '@/Mixins/Market/MarketMixin';

export default Template({
    components: {
        OrderbookChannel,
        MarketTrades,
        OrderForm,
        MarketStats,
        TButton,
        TextInput,
        AppLayout,
        OrderBook,
        OpenOrders,
    },
    mixins: [MarketMixin],
    props: {
        market: Object,
        quotes: [Array, Object],
        futures: Boolean,
        isMobile: Boolean,
        fee: String,
    },
    data() {
        return {
            mobileFirstTab: 'orderbook',
            activeTheme: '',
            chartIteration: 0,
            orderType: 'limit',
        }
    },
    beforeDestroy () {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.onResize, { passive: true })
        }
    },
    computed:{
        chartUrl() {
            let theme = document.getElementById("body").getAttribute("class");

            if(!theme || theme == '')
                theme = "light";

            this.activeTheme = theme;

            let symbol = this.market.data.name;
            let route = false;

            if(this.market.data.chart_enabled == "liquidity") {
                symbol = this.market.data.sanitized_name;
                route = true;
            } else if(this.market.data.chart_enabled == "custom") {
                return "https://s.tradingview.com/widgetembed/?frameElementId=tradingview_c2536&theme=" + (this.activeTheme) + "&symbol=" + (this.market.data.custom_market_path) + "&hidelegend=1&saveimage=0&hidesidetoolbar=1&interval=D&symboledit=1&saveimage=1&toolbarbg=F1F3F6&studies=%5B%5D&hideideas=1&style=1&timezone=Etc%2FUTC&studies_overrides=%7B%7D&overrides=%7B%7D&enabled_features=%5B%5D&disabled_features=%5B%5D&locale=en&utm_medium=widget&utm_campaign=chart&disabled_features=%5B%22use_localstorage_for_settings%22%2C%22hide_left_toolbar_by_default%22%2C%22header_symbol_search%22%2C%22header_saveload%22%2C%22header_undo_redo%22%5D";
            }

            return this.route('chart', { symbol: symbol, route: route});
        },
        markets: function () {
            return this.$store.getters.getMarkets;
        },
    },
    mounted() {

        this.loadFavorites();

        if(_.isEmpty(this.markets)) {
            this.$store.dispatch('fetchMarkets', this.route('markets.api.ticker'));
        }

        this.$worker.$on('themeChanged', (data) => {
            this.chartIteration++;
            this.activeTheme = data;
        });

        window.globalMarket = this.market.data;

        this.onResize();
        window.addEventListener('resize', this.onResize, { passive: true })

    },
    methods: {
        setFirstTab(type) {
            this.mobileFirstTab = type;
        },
        setOrderType(type) {
            this.orderType = type;
            //this.buyErrorField = null;
            //this.sellErrorField = null;
        },
        onResize () {

            if(window.innerWidth < 986) {
                this.$inertia.visit(this.route('market.lite', window.globalMarket.name));
            } else {
                //this.$inertia.visit(this.route('market', window.globalMarket.name));
            }
        }
    }
})
</script>
