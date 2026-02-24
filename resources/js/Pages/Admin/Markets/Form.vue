<script>
import Template from '{Template}/Admin/Pages/Admin/Markets/Form.template'
import AppLayout from '@/Layouts/AdminLayout'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import TextInput from '@/Jetstream/TextInput'
import TextareaInput from "@/Jetstream/TextareaInput";
import LoadingButton from "@/Jetstream/LoadingButton";
import TrashedMessage from "@/Jetstream/TrashedMessage";
import SelectInput from "@/Jetstream/SelectInput";

const defaultForm = {
    name: null,
    quote_currency_id: null,
    base_currency_id: null,
    base_precision: null,
    quote_precision: null,
    min_trade_size: null,
    max_trade_size: null,
    min_trade_value: null,
    max_trade_value: null,
    base_ticker_size: null,
    quote_ticker_size: null,
    status: true,
    switch_chart: false,
    trade_status: true,
    buy_order_status: true,
    sell_order_status: true,
    cancel_order_status: true,
    has_futures: false,
    discount: 0,
    discount_bid: 0,
};

export default Template({
    components: {
        NavButtonLink,
        AppLayout,
        TextInput,
        TextareaInput,
        LoadingButton,
        TrashedMessage,
        SelectInput
    },
    props: {
        errors: Object,
        market: Object,
        isEdit: {
            type: Boolean,
            default: false,
        }
    },
    remember: 'form',
    data() {
        return {
            currencies: {},
            sending: false,
            form: Object.assign({}, defaultForm),
        }
    },
    mounted() {
        this.loadCurrencies();

        if(this.isEdit) {
            this.form = this.market
        }
    },
    computed: {
        subTitle: function () {
            return this.isEdit ? this.market.name : 'Create';
        },
        actionButtonTitle: function () {
            return this.isEdit ? 'Update Market' : 'Create Market';
        },
    },
    methods: {
        submit() {
            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }
            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => this.sending = false,
                onSuccess: () => {
                    this.$toast.open('Market was saved');
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            this.form.base_ticker_size = 1 / Math.pow(10, this.form.base_precision);
            this.form.quote_ticker_size = 1 / Math.pow(10, this.form.quote_precision);

            if(this.isEdit) {
                this.$inertia.put(this.route('admin.markets.update', this.market.id), this.form, afterRequest);
            } else {
                this.$inertia.post(this.route('admin.markets.store'), this.form, afterRequest);
            }
        },
        destroy() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (confirm('Are you sure you want to delete this market?')) {
                this.$inertia.delete(this.route('admin.markets.destroy', this.market.id), {
                    onSuccess: () => { this.$toast.open('Market was deleted'); },
                    onError: () => {
                        this.$toast.error('There are some form errors');
                    }
                })
            }
        },
        restore() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (confirm('Are you sure you want to restore this market?')) {
                this.$inertia.put(this.route('admin.markets.restore', this.market.id))
            }
        },
        loadCurrencies(id) {
            axios.get(this.route('admin.currencies'), {
                params: {
                    'json': true
                }
            }).then((res) => {
                this.currencies = res.data;
            })
        },
    },
});
</script>
