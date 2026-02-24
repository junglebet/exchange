<script>
import Template from '{Template}/Web/Pages/Article/Article.template'
import AppLayout from '@/Layouts/AppLayout'
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
