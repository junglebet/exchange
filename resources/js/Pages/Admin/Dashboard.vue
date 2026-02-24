<script>
    import Template from '{Template}/Admin/Pages/Admin/Dashboard.template'
    import AppLayout from '@/Layouts/AdminLayout'
    import NavButton from "@/Jetstream/NavButton";
    import TextInput from '@/Jetstream/TextInput'
    import TextareaInput from "@/Jetstream/TextareaInput";
    import LoadingButton from "@/Jetstream/LoadingButton";
    import TrashedMessage from "@/Jetstream/TrashedMessage";
    import SelectInput from "@/Jetstream/SelectInput";
    import EmptyColumn from "@/Jetstream/EmptyColumn";
    import Badge from "@/Jetstream/Badge";

    export default Template({
        components: {
            NavButton,
            AppLayout,
            TextInput,
            TextareaInput,
            LoadingButton,
            TrashedMessage,
            SelectInput,
            EmptyColumn,
            Badge
        },
        props: {
            stats: Object,
            services: Array,
            last: String,
        },
        data() {
            return {
                interval: null,
                loading: false,
                socketState: false,
            }
        },
        computed: {
            socketConnection() {
                return window.Echo && window.Echo.connector.socket.connected;
            },
        },
        mounted() {
            setTimeout(() => {
                this.testWebsockets();
            }, 3000);
        },
        beforeDestroy: function(){
            clearInterval( this.interval )
        },
        methods: {
            getList() {
                this.$inertia.replace(this.route('admin.dashboard'), {preserveScroll: true});
            },
            testServices() {

                if(this.loading) return;

                this.loading = true;

                clearInterval( this.interval );

                this.$toast.open('Testing job has started');

                this.interval = setInterval(() => {
                    this.getList();
                }, 5000);

                axios.get(this.route('admin.system.monitor.test')).then(response => {
                    this.loading = false;
                }).catch(error => {
                    this.loading = false;
                });
            },
            testWebsockets() {
                this.services.forEach((service, index) => {
                    if(service.title == "Websockets") {
                        this.services[index].status = this.socketState || this.socketConnection ? "online" : "offline";
                    }
                });
                this.storeWebsockets();
            },
            storeWebsockets() {
                axios.post(this.route('admin.system.monitor.websocket'), {
                    status: this.socketConnection,
                }).then(() => {

                });
            }
        },
    })
</script>
