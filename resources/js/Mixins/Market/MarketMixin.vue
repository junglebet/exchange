<script>
export default {
    methods: {
        loadFavorites() {
            window.marketFavorites = JSON.parse(localStorage.getItem('marketFavorites')) || [];
        },
        favoriteToggle: function(market) {

            let markets = JSON.parse(localStorage.getItem('marketFavorites')) || [];

            if(markets && markets.includes(market.name)) {

                let index = markets.indexOf(market.name);

                if (index > -1) {
                    markets.splice(index, 1);
                }

                localStorage.setItem('marketFavorites', JSON.stringify(markets));
                window.marketFavorites = markets;

                market['favorite'] = true;
                this.$store.dispatch('updateMarket', {
                    market: market
                });

                return;
            }

            markets.push(market.name);

            localStorage.setItem('marketFavorites', JSON.stringify(markets));
            window.marketFavorites = markets;

            market['favorite'] = false;
            this.$store.dispatch('updateMarket', {
                market: market
            });
        },
        getFavorites() {
            return window.marketFavorites;
        },
        isFavorite(market) {
            if(!market || !window.marketFavorites) return false;

            return window.marketFavorites.includes(market);
        },
        setMarket(market, futures = false) {

            if(futures) {
                return this.$inertia.visit(this.route('futures-market', market.name));
            }

            this.$inertia.visit(this.route('market', market.name));
        },
    }
};
</script>
