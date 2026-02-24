<script>
import Template from '{Template}/Web/Pages/MarketLite/Partials/OrderForm.template'
import TextInput from "@/Jetstream/TextInput";
import SelectInput from "@/Jetstream/SelectInput";
import {mapGetters} from "vuex";
import VueSlider from 'vue-slider-component'
import '@/../css/progress-slider/default.css'
import {math_formatter, math_percentage} from "@/Functions/Math";

export default Template({
    components: {
        TextInput,
        SelectInput,
        VueSlider
    },
    props: {
        market: Object,
        fee: String
    },
    data() {
        return {
            openForm: false,
            leverage: 25,
            balance: 0,
            orderType: 'limit',
            bid: {
                price: 0,
                quantity: 0,
                type: 'limit',
                side: 'buy',
                trigger_price: 0,
                total: 0,
            },
            ask: {
                price: 0,
                quantity: 0,
                type: 'limit',
                side: 'sell',
                trigger_price: 0,
                total: 0,
            },
            buySlider: {
                min: 0,
                max: 100,
                interval: 1,
                value: 0,
                marks: [0, 25, 50, 75, 100],
                formatter: '{value}%',
                disabled: false,
            },
            sellSlider: {
                value: 0,
                marks: [0, 25, 50, 75, 100],
                formatter: '{value}%',
                disabled: false,
            },
            errors: null,
            placingBuyOrder: false,
            placingSellOrder: false,
            buyErrorField: false,
            sellErrorField: false,
            activeTab: 'buy',
        }
    },
    mounted() {

        /*
        Order click event listener
         */
        this.$worker.$on('place-order', (data) => {

            // Set price
            if(this.orderType != "market") {
                this.bid.price = this.decimal_format(data.order.price, this.market.quote_precision);
                this.ask.price = this.decimal_format(data.order.price, this.market.quote_precision);
            }

            if(data.order.quantity > this.tradeMaxBuy) {
                this.bid.quantity = this.decimal_format(this.tradeMaxBuy, this.market.base_precision);
            } else {
                this.bid.quantity = this.decimal_format(data.order.quantity, this.market.base_precision);
            }

            if(this.activeTab == "sell") {

                let balance = this.baseWallet ? this.baseWallet.balance_in_wallet : 0;

                if(parseFloat(balance) < parseFloat(data.order.quantity)) {
                    this.ask.quantity = this.decimal_format(balance, this.market.base_precision);
                } else {
                    this.ask.quantity = this.decimal_format(data.order.quantity, this.market.base_precision);
                }

            }
        })

        if(_.isEmpty(this.wallets) && this.$page.props.user) {
            this.$store.dispatch('fetchWallets', this.route('wallets.index'));
        }

        if(this.$page.props.user) {
            this.buySlider.disabled = false;
            this.sellSlider.disabled = false;
        }

        this.bid.price = this.market.last;
        this.ask.price = this.market.last;
    },

    computed: {
        ...mapGetters({
            wallets: 'getWallets'
        }),
        market_stats: function () {
            return this.$store.getters.getMarket(this.market.name) ?? this.market;
        },
        baseWallet: function () {
            if(this.$store.getters.getUser) {
                return this.$store.getters.getWallet(this.market.base_currency);
            }
        },
        quoteWallet: function () {
            if(this.$store.getters.getUser) {
                return this.$store.getters.getWallet(this.market.quote_currency);
            }
        },
        tradeMaxBuy: function () {

            if(!this.bid.price || this.bid.price == 0) return 0;



            let balance = this.quoteWallet ? this.quoteWallet.balance_in_wallet : 0;

            let availableAmount = balance / this.bid.price;

            let feeInAmount = ((this.fee / 100) * balance) / this.bid.price;

            return math_formatter(availableAmount - feeInAmount, this.market.base_precision);
        },
        estimateBidFee: function () {

            if(!this.bid.quantity || this.bid.quantity == 0) return 0;

            return math_formatter((this.fee / 100) * this.bid.total, 8);
        },
        estimateAskFee: function () {

            if(!this.ask.quantity || this.ask.quantity == 0) return 0;

            return math_formatter((this.ask.total) * (this.fee / 100), 8);
        },
    },
    methods: {
        placeBuyOrder() {

            if(!this.bid.quantity || this.bid.quantity == 0) {
                this.buyErrorField = 'quantity';
            }

            if(!this.bid.price || this.bid.price == 0) {
                this.buyErrorField = 'price';
            }

            if(this.buyErrorField) return;

            if(this.placingBuyOrder) return;

            this.buyErrorField = null;
            this.sellErrorField = null;
            this.placingBuyOrder = true;

            if(!this.$page.props.user) {
                return this.$inertia.visit(this.route('login'));
            }

            this.bid.market = this.market.name;
            this.bid.type = this.orderType;

            if(this.bid.type == "market") {
                this.bid.quoteQuantity = this.bid.quantity;
            }

            if(this.bid.type == "stop") {
                this.bid.trigger_condition = 'down'; //this.bid.trigger_condition;
                this.bid.trigger_price = this.bid.trigger_price;
            }

            let formRoute = this.route('orders.store');

            axios.post(formRoute, this.bid).then((response) => {
                this.placingBuyOrder = false;
                this.$toast.success(this.$t("Order Created"));
                this.$store.dispatch('fetchOpenOrders', { market: this.market.name, route: this.route('orders.api.open') });
            }).catch(error => {

                this.placingBuyOrder = false;

                _.each(error.response.data.errors, (field, key) => {

                    if(key == "quoteQuantity") key = "quantity";

                    this.buyErrorField = key;
                    this.$toast.error(field[0]);
                });
            });
        },
        placeSellOrder() {

            if(!this.ask.quantity || this.ask.quantity == 0) {
                this.sellErrorField = 'quantity';
            }

            if(!this.ask.price || this.ask.price == 0) {
                this.sellErrorField = 'price';
            }

            if(this.sellErrorField) return;

            if(this.placingSellOrder) return;

            this.placingSellOrder = true;
            this.buyErrorField = null;
            this.sellErrorField = null;

            if(!this.$page.props.user) {
                return this.$inertia.visit(this.route('login'));
            }

            this.ask.market = this.market.name;
            this.ask.type = this.orderType;

            if(this.ask.type == "market") {
                this.ask.quoteQuantity = this.ask.quantity;
            }

            if(this.ask.type == "stop") {
                this.ask.trigger_condition = 'down'; //this.ask.trigger_condition;
                this.ask.trigger_price = this.ask.trigger_price;
            }

            let formRoute = this.route('orders.store');

            axios.post(formRoute, this.ask).then((response) => {
                this.placingSellOrder = false;
                this.$toast.success(this.$t("Order Created"));
                this.$store.dispatch('fetchOpenOrders', { market: this.market.name, route: this.route('orders.api.open') });
            }).catch(error => {
                this.placingSellOrder = false;
                _.each(error.response.data.errors, (field, key) => {
                    this.sellErrorField = key;
                    this.$toast.error(field[0]);
                });
            });
        },
        setOrderType(type) {
            this.orderType = type;

            this.buySlider.value = 0;
            this.sellSlider.value = 0;

            this.buyErrorField = null;
            this.sellErrorField = null;
        },
        calculateFee(size) {

            if(size == 0) return 0;

            return math_formatter((this.fee / 100) * size, 8);
        },
        multiplier(num1, num2, side, field, precision) {

            if(!num1 || !num2) {

                if (side == "ask") {
                    this.ask.total = 0;
                } else {
                    this.bid.total = 0;
                }

                return;
            }

            let amount = math_formatter((parseFloat(num1) * parseFloat(num2)), precision);

            if(side == "ask") {
                this.ask[field] = amount;
            } else {
                amount = math_formatter((parseFloat(num1) * parseFloat(num2)) + parseFloat((num2 / this.fee) * num1), precision);
                this.bid[field] = amount;
            }
        },
        decimal_format(value, decimal) {
            return math_formatter(value, decimal);
        },
        divider(num1, num2, side, field, precision) {

            if(!num1 || !num2 || num2 == 0) {

                if(side == "ask") {
                    this.ask.total = 0;
                } else {
                    this.bid.total = 0;
                }

                return
            };

            let amount = math_formatter((parseFloat(num1) / parseFloat(num2)) - (this.calculateFee(num1) / num2), precision);

            if(side == "ask") {
                this.ask[field] = amount;
            } else {
                this.bid[field] = amount;
            }
        },
        changeBuySlider(percentage) {

            let balance = this.quoteWallet ? this.quoteWallet.balance_in_wallet : 0;

            let amountWithPercentage = math_percentage(balance, percentage);

            if(this.orderType == 'market') {
                this.bid.quantity = amountWithPercentage;
            } else {

                if(percentage > 0) {
                    this.bid.total = math_formatter(amountWithPercentage, this.market.quote_precision);

                } else {
                    this.bid.total = 0;
                    this.bid.quantity = 0;
                }
                this.divider(this.bid.total, this.bid.price, 'bid', 'quantity', this.market.base_precision)
            }
        },
        changeSellSlider(percentage) {

            let balance = this.baseWallet ? this.baseWallet.balance_in_wallet : 0;

            this.ask.quantity = math_formatter(math_percentage(balance, percentage), this.market.quote_precision);
        },
        handleInput ($event, side, field) {

            let keyCode = ($event.keyCode ? $event.keyCode : $event.which);

            if ((keyCode < 48 || keyCode > 57) && (keyCode !== 46 || this.getFormData(side, field).indexOf('.') != -1)) {
                $event.preventDefault();
            }

            let precision = this.market.quote_precision;

            if(field == "quantity") {
                precision = this.market.base_precision;
            }

            // restrict to 2 decimal places
            if(this.getFormData(side, field) != null && this.getFormData(side, field).indexOf(".")>-1 && (this.getFormData(side, field).split('.')[1].length >= 99)){
                $event.preventDefault();
            }
        },
        clearInput ($event, side, field) {
            if(this.getFormData(side, field).charAt(0) == '.') {

                if(field == 'balance') {
                    this.balance = 0;
                    return;
                }

                this.setFormData(side, field, 0);
            }
        },
        getFormData(side, field) {

            if(field == 'balance') {
                return this.balance.toString();
            }

            if(side == 'buy') {
                return this.bid[field].toString();
            }
            return this.ask[field].toString();
        },
        setFormData(side, field, value) {
            if(side == 'buy') {
                this.bid[field] = value;
            }
            this.ask[field] = value;
        },
        setTab(side) {
            this.openForm = true;
            this.activeTab = side;
        },
        setLeverage(value) {
            this.leverage = value;
        }
    },
    watch: {
        orderType: function(type) {
            if(type == 'market') {
                //this.bid.price = '';
                //this.ask.price = '';
            }
        },
        'ask.quantity'(newVal){

            let balance = this.baseWallet ? this.baseWallet.balance_in_wallet : 0;

            newVal = newVal.toString();

            this.multiplier(this.ask.price, this.ask.quantity, 'ask', 'total', this.market.quote_precision)

            if (newVal.includes('.')) {
                this.ask.quantity = newVal.split('.')[0] + '.' + newVal.split('.')[1].slice(0, this.market.base_precision)
            }

            if(this.ask.quantity > parseFloat(balance)) {
                this.sellErrorField = "quantity";
                this.$toast.error(this.$t('Your balance is not enough.'))
            } else {
                this.sellErrorField = null;
            }
        },
        'ask.price'(newVal){

            newVal = newVal.toString();

            this.multiplier(this.ask.price, this.ask.quantity, 'ask', 'total', this.market.quote_precision)

            if (newVal.includes('.')) {
                this.ask.price = newVal.split('.')[0] + '.' + newVal.split('.')[1].slice(0, this.market.quote_precision)
            }

            if(parseFloat(newVal) > 0 && this.sellErrorField == 'price') {
                this.sellErrorField = '';
            }
        },
        'bid.quantity'(newVal){

            let balance = this.quoteWallet ? this.quoteWallet.balance_in_wallet : 0;

            newVal = newVal.toString();

            this.multiplier(this.bid.price, this.bid.quantity, 'bid', 'total', this.market.quote_precision)

            if (newVal.includes('.')) {
                this.bid.quantity = newVal.split('.')[0] + '.' + newVal.split('.')[1].slice(0, this.market.base_precision)
            }


            if(this.orderType !== 'market') {
                if (this.bid.quantity > this.tradeMaxBuy && this.bid.price) {
                    this.buyErrorField = "quantity";
                    this.$toast.error(this.$t('Your balance is not enough.'))
                } else {
                    this.buyErrorField = null;
                }
            }

            if(this.orderType === 'market') {
                if (this.bid.quantity > parseFloat(balance)) {
                    this.buyErrorField = "quantity";
                    this.$toast.error(this.$t('Your balance is not enough.'))
                } else {
                    this.buyErrorField = null;
                }
            }
        },
        'bid.price'(newVal){

            newVal = newVal.toString();

            this.multiplier(this.bid.price, this.bid.quantity, 'bid', 'total', this.market.quote_precision)

            if (newVal.includes('.')) {
                this.bid.price = newVal.split('.')[0] + '.' + newVal.split('.')[1].slice(0, this.market.quote_precision)
            }

            if(parseFloat(newVal) > 0 && this.buyErrorField == 'price') {
                this.buyErrorField = '';
            }
        },
    }
})
</script>
