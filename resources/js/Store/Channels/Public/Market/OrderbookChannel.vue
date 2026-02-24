<template>
    <div></div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
    name: 'orderbook-channel',
    data() {
        return {
            channel: 'orderbook-',
        }
    },
    props: {
        market: Object,
    },
    mounted() {
      // Join when single market page loaded
      this.join();
    },
    watch: {
        socket: function () {
            // Rejoin when socket updated
            this.join();
        },
    },
    computed: mapGetters({
        socket: 'getSocket',
    }),
    methods: {
        join: function () {
            let mergedChannel = this.channel + this.market.name;

            // If already joined ignore
            if(mergedChannel in window.Echo.connector.channels) return false;

            //Join to the channel and listen events
            window.Echo.channel(mergedChannel)
                .listen('OrderBookUpdated', (payload) => {

                    this.$store.dispatch('updateOrderbook', {
                        order: payload.order,
                        type: payload.type,
                        market: this.market.name,
                    });

                }).listen('OrderBookSnapshot', (payload) => {

                    let orders = [];

                    orders['bids'] = payload.bids;
                    orders['asks'] = payload.asks;

                    this.$store.dispatch('liquidityOrders', { orders: orders, market: this.market.name });
                });
        }
    }
}
</script>
