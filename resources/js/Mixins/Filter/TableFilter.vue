<script>
export default {
    data() {
        return {
            filter: {
                search: '',
                sortBy: null,
                defaultFilter: 'name',
                defaultFilterDirection: 'asc',
                filterBy: null,
                filterDirection: null,
            },
        }
    },
    methods: {
        setFilter(filterBy, direction = false, initial = false) {

            if(!initial && this.filter.filterBy != filterBy) {
                this.filter.filterBy = filterBy;
                this.filter.filterDirection = this.filter.defaultFilterDirection;
                return;
            }

            this.filter.filterBy = filterBy;

            if(direction) {
                this.filter.filterDirection = direction;
            } else if(this.filter.filterDirection == "none") {
                this.filter.filterDirection = 'asc';
            } else if(this.filter.filterDirection == 'asc') {
                this.filter.filterDirection = 'desc';
            } else {
                this.filter.filterDirection = 'none';
                this.filter.filterBy = this.filter.defaultFilter;
            }
        },
        setSort(sortBy) {
            this.filter.sortBy = sortBy;
        }
    }
};
</script>
