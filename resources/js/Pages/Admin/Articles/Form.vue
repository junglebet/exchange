<script>
import Template from '{Template}/Admin/Pages/Admin/Articles/Form.template'
import AppLayout from '@/Layouts/AdminLayout'
import NavButtonLink from "@/Jetstream/NavButtonLink";
import TextInput from '@/Jetstream/TextInput'
import TextareaInput from "@/Jetstream/TextareaInput";
import LoadingButton from "@/Jetstream/LoadingButton";
import TrashedMessage from "@/Jetstream/TrashedMessage";
import SelectInput from "@/Jetstream/SelectInput";
import EmptyColumn from "@/Jetstream/EmptyColumn";
import { quillEditor } from 'vue-quill-editor'
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

const defaultForm = {
    title: null,
    slug: null,
    body: null,
    status: true,
    featured: false,
    language: null,
    category_id: null,
    file_id: null,
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
        article: Object,
        categories: Array,
        languages: Object,
    },
    remember: 'form',
    data() {
        return {
            sending: false,
            form: Object.assign({}, defaultForm),
            articleLogo: null,
            uploaded: false,
            uploadUrl: this.route('file-upload'),
            uploadHeaders: {
                'X-XSRF-TOKEN' : $cookies.get('XSRF-TOKEN')
            }
        }
    },
    mounted() {
        if(this.isEdit) {
            this.form = this.article;

            if(this.form.file) {
                this.articleLogo = {
                    name: this.form.file.name,
                    type: this.form.file.type,
                    url: this.form.file.url
                }
            }
        }
    },
    computed: {
        subTitle: function () {
            return this.isEdit ? this.article.title : 'Create';
        },
        actionButtonTitle: function () {
            return this.isEdit ? 'Update Article' : 'Create Article';
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
                    this.$toast.open('Database updated');
                },
                onError: () => {
                    this.$toast.error('There are some form errors');
                }
            };

            if(this.isEdit) {
                this.$inertia.put(this.route('admin.articles.update', this.article.id), this.form, afterRequest);
            } else {
                this.$inertia.post(this.route('admin.articles.store'), this.form, afterRequest);
            }
        },
        destroy() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (confirm('Are you sure you want to delete this article?')) {
                this.$inertia.delete(this.route('admin.articles.destroy', this.article.id), {
                    onSuccess: () => { this.$toast.open('Article was deleted'); }
                })
            }
        },
        restore() {

            if(this.$page.props.mode == "readonly") {
                return this.$toast.warning('In Demo Version we enabled READ ONLY mode to protect our demo content.')
            }

            if (confirm('Are you sure you want to restore this article?')) {
                this.$inertia.put(this.route('admin.articles.restore', this.article.id))
            }
        },
        removePic: function(){
            let articleLogo = this.articleLogo;
            this.$refs.fileUploadForm.deleteUpload(this.uploadUrl, this.uploadHeaders, [articleLogo], {
                uuid: this.form.file_id,
            });
            this.articleLogo = null;
            this.form.file_id = null;
            this.uploaded = false;
        },
        upload: function(){
            let self = this;
            this.$refs.fileUploadForm.upload(this.uploadUrl, this.uploadHeaders, [this.articleLogo]).then(function(){
                self.uploaded = true;
                setTimeout(function(){
                    // self.articleLogo.progress(0);
                }, 500);
            });
        },
        onSelect: function(fileRecords){
            this.upload();
            this.uploaded = false;
        },
        onUpload: function(responses){
            let response = responses[0];
            if (!response.error) {
                this.form.file_id = response.data.uuid;
            }
        },
        generateSlug() {
            this.form.slug = this.slugify(this.form.title);
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
        }
    },
    watch: {

    }
});
</script>
