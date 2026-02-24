import Vue from 'vue'

import {MARKET_LIST, MARKET_TRADE_LIST, MARKET_UPDATE, MARKET_UPDATE_STATS, MARKET_TRADE_STORE} from "@/Store/Mutations/Market";

const state = {
    loading: false,
    items: [],
    orders: [],
    trades: []
};

const getters = {
    getMarkets: (state) => {
        return state.items;
    },
    getMarket: (state) => (name) => {
        return state.items.find((market) => {
            return market.name === name;
        });
    },
    getMarketTrades: (state) => (market) => {
        return state.trades[market] || [];
    },
};

const mutations = {
    [MARKET_LIST](state, {markets}) {
        state.items = markets;
    },
    [MARKET_UPDATE](state, {market}) {
        const index = state.items.findIndex(item => item.name === market.name)
        Vue.set(state.items, index, market);
    },
    [MARKET_UPDATE_STATS](state, {market}) {

        const index = state.items.findIndex(item => item.name === market.name)

        let tempMarket = state.items[index];

        tempMarket.last = market.last;
        tempMarket.low = market.low;
        tempMarket.high = market.high;
        tempMarket.volume = market.volume;
        tempMarket.change = market.change;

        Vue.set(state.items, index, tempMarket);
    },
    [MARKET_TRADE_LIST](state, {trades, market}) {
        Vue.set(state.trades, market, trades);
    },
    [MARKET_TRADE_STORE](state, {trade, market}) {

        if(!state.trades[market]) {
            Vue.set(state.trades, market, []);
        }

        state.trades[market].unshift(trade);
    },
};

const actions = {
    fetchMarkets({ state, commit }, route) {
        axios.get(route).then(res => {
            commit(MARKET_LIST, {
                markets: res.data.data
            });
        })
    },
    updateMarket({ state, commit }, payload) {
        commit(MARKET_UPDATE, payload);
    },
    updateMarketStats({ state, commit }, payload) {
        commit(MARKET_UPDATE_STATS, payload);
    },
    updateMarketTrade({ state, commit }, payload) {
        commit(MARKET_TRADE_STORE, {
            trade: payload.trade,
            market: payload.market.name
        });
    },
    fetchMarketTrades({ state, commit }, { market, route }) {
        axios.get(route, {
            params: {
                'market' : market,
            }
        }).then(res => {
            commit(MARKET_TRADE_LIST, {
                trades: res.data.data,
                market: market,
            });
        })
    },
    setMarketTrades({ state, commit }, { market, trades }) {
        commit(MARKET_TRADE_LIST, {
            trades: trades,
            market: market,
        });
    },
};

export default {
    namespace: true,
    state,
    getters,
    actions,
    mutations
}
