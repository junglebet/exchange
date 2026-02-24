<script>
import Template from '{Template}/Web/Components/LanguageSwitcher.template'
export default Template({
    data() {
        return {
            defaultLanguage: window.LANGUAGE
        }
    },
    methods: {
        switchLanguage(slug) {
            axios.get(this.route('language.set'), {
                params: {
                    language: slug
                }
            }).then((response) => {
                window.location.reload();
            }).catch(error => {

            });
        },
    }
})
</script>
