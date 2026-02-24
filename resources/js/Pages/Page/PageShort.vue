<script>
import Template from '{Template}/Web/Pages/Page/PageShort.template'
import AppLayout from '@/Layouts/AppLiteLayout'
import TextInput from "@/Jetstream/TextInput";
import TButton from "@/Jetstream/Button";

export default Template({
    components: {
        TButton,
        TextInput,
        AppLayout,
    },
    props: {
        page: Object,
    },
    computed:{
        cleanMessage() {
            return this.page.content;
        }
    }
})
</script>
