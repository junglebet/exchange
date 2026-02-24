<script>
import Template from '{Template}/Admin/Pages/Admin/Pages/Form.template'
import AppLayout from '@/Layouts/AdminLayout'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import TextInput from '@/Jetstream/TextInput'
import TextareaInput from "@/Jetstream/TextareaInput";
import LoadingButton from "@/Jetstream/LoadingButton";
import TrashedMessage from "@/Jetstream/TrashedMessage";
import SelectInput from "@/Jetstream/SelectInput";
import EmptyColumn from "@/Jetstream/EmptyColumn";
import {quillEditor} from "vue-quill-editor";
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

const defaultForm = {
    title: null,
    slug: null,
    content: null,
    seo_title: null,
    seo_description: null,
    seo_keywords: null,
    status: null,
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
        EmptyColumn,
        quillEditor
    },
    props: {
        isEdit: {
            type: Boolean,
            default: false,
        },
        errors: Object,
        page: Object,
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
            this.form = this.page;
        }
    },
    computed: {
        subTitle: function () {
            return this.isEdit ? this.page.title : 'Create';
        },
        actionButtonTitle: function () {
            return this.isEdit ? 'Update Page' : 'Create Page';
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
                    this.$toast.open('Database Updated');
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            if(this.isEdit) {
                this.$inertia.put(this.route('admin.pages.update', this.page.id), this.form, afterRequest);
            } else {
                this.$inertia.post(this.route('admin.pages.store'), this.form, afterRequest);
            }
        },
        destroy() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (confirm('Are you sure you want to delete this page?')) {
                this.$inertia.delete(this.route('admin.pages.destroy', this.page.id), {
                    onSuccess: () => { this.$toast.open('Page was deleted'); },
                    onError: () => {
                        this.$toast.error('There are some form errors');
                    }
                })
            }
        },
        slugify (text, ampersand = 'and') {
            const a = 'àáäâèéëêìíïîòóöôùúüûñçßÿỳýœæŕśńṕẃǵǹḿǘẍźḧ'
            const b = 'aaaaeeeeiiiioooouuuuncsyyyoarsnpwgnmuxzh'
            const p = new RegExp(a.split('').join('|'), 'g')

            return text.toString().toLowerCase()
                .replace(/[\s_]+/g, '-')
                .replace(p, c =>
                    b.charAt(a.indexOf(c)))
                .replace(/&/g, `-${ampersand}-`)
                .replace(/[^\w-]+/g, '')
                .replace(/--+/g, '-')
                .replace(/^-+|-+$/g, '')
        },
        handleImageAdded: function(file, Editor, cursorLocation, resetUploader) {

            let formData = new FormData();
            formData.append("file", file);

            axios({
                url: this.route('file-upload'),
                method: "POST",
                data: formData
            })
                .then(result => {
                    let url = result.data.path;
                    Editor.insertEmbed(cursorLocation, "image", url);
                    resetUploader();
                })
                .catch(err => {

                });
        }
    },
    watch: {
        'form.title': function (type, newType) {
            if(!this.isEdit) {
                this.form.slug = this.slugify(this.form.title)
            }
        },
    }
});
</script>
