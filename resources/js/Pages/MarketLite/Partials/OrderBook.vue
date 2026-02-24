<script>
import Template from '{Template}/Web/Pages/Market/Partials/OrderBook.template'
import {math_formatter, math_percentage_of_number} from "@/Functions/Math";

export default Template({
    props: {
        market: Object,
    },
    data() {
        return {
            limit: 14,
            fetchInterval: null,
        }
    },
    mounted() {
        if(!this.market.s) {
            this.fetchOrderbook();
        }
    },
    beforeDestroy() {
        clearInterval(this.fetchInterval);
    },
    computed: {
        bids: function () {
            return _.take(_.orderBy(this.$store.getters.getOrderbook(this.market.name, 'bids'), (item) => {
                return parseFloat(item.price);
            }, 'desc'), this.limit);
        },
        asks: function () {
            return _.take(_.orderBy(this.$store.getters.getOrderbook(this.market.name, 'asks'), (item) => {
                return parseFloat(item.price);
            }, 'asc'), this.limit).reverse();
        },
        market_stats: function () {
            return this.$store.getters.getMarket(this.market.name) ?? this.market;
        },
        sumBuyQuantity: function () {
            return _.sumBy(_.take(this.bids, this.limit), function(order) { return parseFloat(order.quantity * order.price); });
        },
        sumSellQuantity: function () {
            return _.sumBy(_.take(this.asks, this.limit), function(order) { return parseFloat(order.quantity); });
        },
        myOrders: function () {
            return _.take(_.orderBy(this.$store.getters.getOpenOrders(this.market.name), 'created_at', 'desc'), this.limit);
        },
    },
    methods: {
        fetchOrderbook() {
            this.$store.dispatch('fetchOrders', { market: this.market.name, route: this.route('markets.api.orderbook') });
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
        calculateAmountBar(order, side) {

            let total = '';
            let amount = '';

            if(side == 'buy') {
                total = this.sumBuyQuantity;
                amount = parseFloat(order.quantity * order.price);
            } else {
                total = this.sumSellQuantity;
                amount = order.quantity;
            }

            return math_percentage_of_number(amount, total) + '%';
        },
        handleOrder(order, side) {
            this.$worker.$emit('place-order', {
                order: order,
                side: side,
            });
        },
        isMyOrderPrice(price, side) {
            return this.myOrders.find(x => x.price == price && x.side == side)
        }
    }
})
</script>
