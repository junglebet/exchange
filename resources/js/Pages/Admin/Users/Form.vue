<script>
import Template from '{Template}/Admin/Pages/Admin/Users/Form.template'
import AppLayout from '@/Layouts/AdminLayout'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import TextInput from '@/Jetstream/TextInput'
import TextareaInput from "@/Jetstream/TextareaInput";
import LoadingButton from "@/Jetstream/LoadingButton";
import TrashedMessage from "@/Jetstream/TrashedMessage";
import SelectInput from "@/Jetstream/SelectInput";
import EmptyColumn from "@/Jetstream/EmptyColumn";

const defaultForm = {
    name: null,
    deactivated: null,
};

export default Template({
    components: {
        NavButtonLink,
        AppLayout,
        TextInput,
        TextareaInput,
        LoadingButton,
        TrashedMessage,
        SelectInput,
        EmptyColumn
    },
    props: {
        isEdit: {
            type: Boolean,
            default: false,
        },
        errors: Object,
        model: Object,
    },
    remember: 'form',
    data() {
        return {
            sending: false,
            form: Object.assign({}, defaultForm),
        }
    },
    mounted() {
        if(this.isEdit) {
            this.form = this.model;
        }
    },
    computed: {
        subTitle: function () {
            return this.model.name;
        },
        actionButtonTitle: function () {
            return 'Update User';
        },
    },
    methods: {
        destroy() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (confirm('Are you sure you want to delete this user?')) {
                this.$inertia.delete(this.route('admin.users.destroy', this.model.id), {
                    onSuccess: () => { this.$toast.open('User was deleted'); }
                })
            }
        },
        submit() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => this.sending = false,
                onSuccess: () => {
                    this.$toast.open('User was updated');
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            this.$inertia.put(this.route('admin.users.update', this.model.id), this.form, afterRequest);
        },
    },
});
</script>
