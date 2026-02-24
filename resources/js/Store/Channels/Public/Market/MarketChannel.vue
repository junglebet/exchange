<template>
    <div></div>
</template>

<script>
import {mapGetters} from 'vuex'
import {math_formatter, math_percentage} from "@/Functions/Math";

export default {
    name: 'market-channel',
    data() {
        return {
            channel: 'market',
        }
    },
    computed: mapGetters({
        user: 'getUser',
        socket: 'getSocket'
    }),
    watch: {
        socket: function (val) {
            this.join();
        }
    },
    methods: {
        join: function () {
            //Join to the channel and listen events
            Echo.channel(this.channel)
                .listen('MarketStatsUpdated', (payload) => {
                this.$store.dispatch('updateMarket', {
                    market: payload.market
                });
            }).listen('MarketStatsLiteUpdated', (payload) => {
                this.$store.dispatch('updateMarketStats', {
                    market: payload.market
                });
            }).listen('MarketTradeUpdated', (payload) => {
                if(!payload.s) {
                    this.$store.dispatch('updateMarketTrade', {
                        market: payload.market,
                        trade: payload.trade
                    });
                }
            });
        },
        math_formatter(value, decimals) {
            return math_formatter(value, decimals);
        },
    }
}
</script>
