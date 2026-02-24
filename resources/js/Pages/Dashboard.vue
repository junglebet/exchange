<script>
    import Template from '{Template}/Web/Pages/Dashboard.template'
    import AppLayout from '@/Layouts/AppLayout'
    import Welcome from '@/Jetstream/Welcome'
    import LanguageSwitcher from '@/Components/LanguageSwitcher'
    import MarketMixin from "@/Mixins/Market/MarketMixin";
    import { Hooper, Slide } from 'hooper';
    import 'hooper/dist/hooper.css';

    export default Template({
        components: {
            AppLayout,
            Welcome,
            LanguageSwitcher,
            Hooper,
            Slide
        },
        data() {

            return {
                numAbbr: '',
                sliderElements: 4,
                isMobile: false,
                hooperSettings: {
                    itemsToShow: 4,
                    vertical: false,
                    autoPlay: true,
                    touchDrag: true,
                    mouseDrag: true,
                    wheelControl: false,
                    playSpeed: 3000,
                    infiniteScroll: true,
                    breakpoints: {
                        270: {
                            itemsToShow: 2,
                        },
                        700: {
                            itemsToShow: 3,
                        },
                        900: {
                            itemsToShow: 4,
                        },
                    }
                }
            }
        },
        props: {
            articles: Object,
        },
        created() {
            window.addEventListener("resize", this.sliderListener);
        },
        destroyed() {
            window.removeEventListener("resize", this.sliderListener);
        },
        methods: {
            sliderListener() {
                if(window.innerWidth >= 670) {
                    this.sliderElements = 3;
                    this.isMobile = false;
                } else {
                    this.sliderElements = 2;
                    this.isMobile = false;
                }
            },
            setArticle(article) {
                this.$inertia.visit(this.route('article', article.id));
            },
        },
        computed: {
            sliderMarkets: function () {

                let markets = this.$store.getters.getMarkets;

                if(markets && markets.length) {
                    return _.chunk(_.orderBy(markets, (market) => {
                        return parseFloat(market['last']);
                    }, 'desc'), this.sliderElements);
                }
            },
            topMarkets: function () {

                let markets = this.$store.getters.getMarkets;

                if(markets && markets.length) {
                    return _.take(_.orderBy(markets, (market) => {
                        return parseFloat(market['last']);
                    }, 'desc'), 10);
                }
            },
        },
        mixins: [MarketMixin],
        mounted() {

            this.numAbbr = require('number-abbreviate');
            //this.sliderListener();

            if(_.isEmpty(this.markets)) {
                this.$store.dispatch('fetchMarkets', this.route('markets.api.ticker'));
            }

            if(!this.$page.props.alt) {
                window.TyperSetup();
            }
        }
    });
</script>
