<script>
import Template from '{Template}/Web/Pages/Article/ArticleShort.template'
import AppLayout from '@/Layouts/AppLiteLayout'
import IconFilter from "@/Components/Table/IconFilter";

export default Template({
    components: {
        AppLayout,
        IconFilter,
    },
    data() {
        return {
            state: false,
            sending: false,
        }
    },
    props: {
        article: Object,
    },
    methods: {

    },
    computed:{
        cleanMessage() {
            return this.article.body;
        }
    }
})
</script>
