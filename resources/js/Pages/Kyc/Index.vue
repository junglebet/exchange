<script>
import Template from '{Template}/Web/Pages/Kyc/Index.template'
import AppLayout from '@/Layouts/AppLayout'
import TextUserInput from "@/Jetstream/TextUserInput";
import TButton from "@/Jetstream/Button";
import LoadingButton from "@/Jetstream/LoadingButton";

const defaultForm = {
    first_name: "",
    last_name: "",
    middle_name: "",
    country_id: null,
    document_type: 'id',
    front_id: null,
    selfie_id: null,
    back_id: null,
};

export default Template({
    components: {
        TButton,
        LoadingButton,
        TextUserInput,
        AppLayout,
    },
    data() {
        return {
            uploaded: false,
            uploadedSelfie: false,
            uploadedBack: false,
            documentPhoto: null,
            selfiePhoto: null,
            backPhoto: null,
            sending: false,
            sendingSelfie: false,
            sendingBack: false,
            form: Object.assign({}, defaultForm),
            uploadUrl: this.route('user-file-upload'),
            uploadHeaders: {
                'X-XSRF-TOKEN' : $cookies.get('XSRF-TOKEN')
            }
        }
    },
    computed: {
        isMultiple: function() {
            return this.form.document_type == "id" || this.form.document_type == "residence_permit";
        },
        actionButtonTitle: function () {
            return 'Submit';
        },
    },
    props: {
        isVerified: Boolean,
        errors: Object,
        countries: Object,
        pendingDocument: Object,
        rejectedDocument: Object,
    },
    methods: {
        submit() {

            if(this.sending) return;

            this.sending = true;

            let afterRequest = {
                onStart: () => this.sending = true,
                onFinish: () => this.sending = false,
                onSuccess: () => {
                    this.sending = false;
                    this.$toast.open(this.$t('KYC Documents were submitted'));
                },
                onError: () => {
                    this.sending = false;
                    this.$toast.error(this.$t('There are some form errors'));
                },
                preserveScroll: true,
            };

            this.$inertia.post(this.route('user.kyc.store'), this.form, afterRequest);
        },
        removeBackPic: function(){
            this.backPhoto = null;
            this.form.back_id = null;
            this.uploadedBack = false;
        },
        removeSelfiePic: function(){
            this.selfiePhoto = null;
            this.form.selfie_id = null;
            this.uploadedSelfie = false;
        },
        removePic: function(){
            this.documentPhoto = null;
            this.form.front_id = null;
            this.uploaded = false;
        },
        upload: function(){
            let self = this;
            this.$refs.fileUploadForm.upload(this.uploadUrl, this.uploadHeaders, [this.documentPhoto]).then(function(){
                self.uploaded = true;
            });
        },
        uploadSelfie: function(){
            let self = this;
            this.$refs.selfieUploadForm.upload(this.uploadUrl, this.uploadHeaders, [this.selfiePhoto]).then(function(){
                self.uploadedSelfie = true;
            });
        },
        uploadBack: function(){
            let self = this;
            this.$refs.backUploadForm.upload(this.uploadUrl, this.uploadHeaders, [this.backPhoto]).then(function(){
                self.uploadedBack = true;
            });
        },
        onSelect: function(fileRecords){
            this.upload();
            this.uploaded = false;
        },
        onSelectSelfie: function(fileRecords){
            this.uploadSelfie();
            this.uploadedSelfie = false;
        },
        onSelectBack: function(fileRecords){
            this.uploadBack();
            this.uploadedBack = false;
        },
        onUpload: function(responses){
            let response = responses[0];
            if (response && !response.error) {
                this.form.front_id = response.data.uuid;
            }
        },
        onUploadSelfie: function(responses){
            let response = responses[0];
            if (response && !response.error) {
                this.form.selfie_id = response.data.uuid;
            }
        },
        onUploadBack: function(responses){
            let response = responses[0];
            if (response && !response.error) {
                this.form.back_id = response.data.uuid;
            }
        }
    },
    watch: {
        'form.document': function (type, newType) {
            this.documentPhoto = null;
            this.form.front_id = null;
            this.form.back_id = null;
        },
    }
})
</script>
