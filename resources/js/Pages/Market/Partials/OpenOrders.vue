<script>
import Template from '{Template}/Web/Pages/Market/Partials/OpenOrders.template'
import {math_formatter} from "@/Functions/Math";
import Vue from "vue";

export default Template({
    props: {
        market: Object,
    },
    data() {
        return {
            openOrdersInterval: null,
            limit: 20,
        }
    },
    mounted() {
        if(this.$page.props.user) {
            this.fetchOpenOrders();
        }
    },
    beforeDestroy: function(){
        clearInterval(this.openOrdersInterval)
    },
    computed: {
        orders: function () {
            return _.take(_.orderBy(this.$store.getters.getOpenOrders(this.market.name), 'created_at', 'desc'), this.limit);
        },
    },
    methods: {
        cancelOrder(order) {

            let form = {
                'uuid': order.id
            };

            let formRoute = this.route('orders.api.cancel');

            let orders = this.$store.getters.getOpenOrders(this.market.name);

            let findCancelledOrder = orders.map(function (x) {
                return x.id;
            }).indexOf(order.id);

            if (findCancelledOrder > -1) {
                Vue.delete(orders, findCancelledOrder);
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
