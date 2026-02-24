<script>
import Template from '{Template}/Web/Pages/MarketLite/Partials/OpenOrders.template'
import {math_formatter} from "@/Functions/Math";

export default Template({
    props: {
        market: Object,
        futures: Boolean,
    },
    data() {
        return {
            openOrdersInterval: null,
            limit: 20,
        }
    },
    mounted() {
        if(this.$page.props.user) {
            if(this.futures) {
                this.fetchFuturesOpenOrders();
                // this.openFuturesOrdersInterval = setInterval(() => {
                //     this.fetchFuturesOpenOrders();
                // }, 2000);
            } else {
                this.fetchOpenOrders();
                // this.openOrdersInterval = setInterval(() => {
                //     this.fetchOpenOrders();
                // }, 5000);
            }
        }
    },
    // beforeDestroy: function(){
    //     clearInterval(this.openOrdersInterval)
    //     clearInterval(this.openFuturesOrdersInterval)
    // },
    computed: {
        orders: function () {
            return _.take(_.orderBy(this.$store.getters.getOpenOrders(this.market.name), 'created_at', 'desc'), this.limit);
        },
        futuresOrders: function () {
            return _.take(_.orderBy(this.$store.getters.getFuturesOpenOrders(this.market.name), 'created_at', 'desc'), this.limit);
        },
    },
    methods: {
        cancelOrder(order) {

            let form = {
                'uuid': order.id
            };

            let formRoute = this.route('orders.api.cancel');

            if(this.futures) {
                formRoute = this.route('orders.api.futures.cancel');
            }

            axios.post(formRoute, form).then((response) => {
                this.$toast.open(this.$t('Order cancelled'));
            }).catch(error => {

            });
        },
        fetchOpenOrders() {
            this.$store.dispatch('fetchOpenOrders', {market: this.market.name, route: this.route('orders.api.open')});
        },
        decimal_format(value, decimal, type = '') {

            let formatted = math_formatter(value, decimal);

            if(type == "fiat") {
                formatted = numeral(formatted).format('0,0.00');
            }

            return formatted;
        },
    }
})
</script>
