<template>
    <div>
        <div class="list-head">
            <div class="form-floating">
                <select v-on:change="fillList('')" class="form-select" id="sortFilter" v-model="sort">
                    <option v-for="item in sorts" :value="item">{{ $t(item)}}</option>
                </select>
                <label for="sortFilter">{{ $t('Sort') }}</label>
            </div>
            <div class="form-floating">
                <select v-on:change="fillList('')" class="form-select" id="directionFilter" v-model="sort_direction">
                    <option value="desc">{{ $t('Descendent') }}</option>
                    <option value="asc">{{ $t('Upward') }}</option>
                </select>
                <label for="directionFilter">{{ $t('Direction') }}</label>
            </div>
            <div class="input-group" style="max-width:400px">
                <span class="input-group-text btn_link_span">
                    <i v-on:click="fillList('')" class="fa-solid fa-rotate"></i>
                </span>
                <input style="padding:28px" type="search" class="form-control search_form" :placeholder="$t('Search')"
                       v-model="search" v-on:keyup="fillList('')">
            </div>
        </div>
        <div class="file-card-background">
            <div v-for="item in pagination.data" v-if="!progress_list">
                <div class="card file-card-container">
                    <button class="btn btn-dark" v-on:click="showDetails(item)" type="button"
                            style="border-radius: 0px; position:absolute; right:0">
                        <i class="fa-solid fa-maximize"></i>
                    </button>
                    <div :class="'file-card' + (item.selected ? ' file-active' : '')"
                         :style="`background-image:url('` + this.getImageFromFile(item)" v-on:click="setSelection(item)">
                    </div>
                </div>
                <div class="file-item">
                    <p>
                        {{item.name}}
                        <br>
                        <small>{{(new Date(item.created_at)).toLocaleString()}}</small>
                    </p>
                </div>
            </div>
            <div v-if="progress_list" class="interface_loader"></div>
        </div>

        <nav aria-label="Page navigation" class="nav justify-content-end" v-if="pagination.last_page > 1"
             style="margin-top: 20px ">
            <ul class="pagination">
                <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                    <button class="page-link" v-if="link.url !== null" v-on:click="fillList(link.url)" type="button"
                            v-html="$t(link.label)"></button>
                </li>
            </ul>
        </nav>
        <nav class="nav justify-content-start" v-if="this.multiple != null && this.selectable">
            <ul class="pagination">
                <button id="markAsSelectedButton" class="btn btn-primary"
                        :disabled="this.required && this.selected.length === 0" type="button"
                        @click="$emit('onSelected', this.selected)">
                    {{ $t('Mark as selected') }}
                </button>
            </ul>
        </nav>
    </div>
</template>
<script>
import cts from "../Constants";

export default {
    props: ['url', 'multiple', 'sorts', 'quantity', 'selectable', 'is_mobile', 'itemsSelected'],
    name: "FileListComponent",
    data() {
        return {
            Cts: cts,
            search: "",
            pagination: [],
            progress_list: false,
            sort: 'created_at',
            sort_direction: 'desc',
            selected : [],
            required : false
        }
    },
    mounted: function () {
        this.fillList('');
        if(this.itemsSelected === null || this.itemsSelected === undefined) return;
        if(Array.isArray(this.itemsSelected)) this.selected = [].concat(this.itemsSelected);
        else this.selected = [this.itemsSelected];
    },
    methods: {
        getImageFromFile: function(file){
            if(file.images.length >0){
                return file.images[0].small;
            }
            let fmt = file.mimetype;
            let image = "/images/mimetypes/";
            if(fmt.includes('pdf'))   return `${image}pdf.png`;
            if(fmt.includes('video')) return `${image}video.png`;
            if(fmt.includes('audio')) return `${image}audio.png`;
            if(fmt.includes('rar') || fmt.includes('zip') || fmt.includes('gzip')) return `${image}compressed.png`;
            if(fmt.includes('word')) return `${image}word.png`;
            if(fmt.includes('powerpoint') || fmt.includes('presentation')) return `${image}powerpoint.png`;
            if(fmt.includes('excel')) return `${image}excel.png`;
            return `${image}text.png`;
        },
        fillList: function (uri) {
            this.pagination = [];
            this.progress_list = true;
            fetch(this.encodeURL(uri)).then(response => response.json()).then((data) => {
                data.data = data.data.map((the_file) => {
                    if(this.selected.length === 0) the_file.selected = false;
                    else the_file.selected = this.selected.some((item) => item.id === the_file.id);
                    return the_file;
                });
                this.pagination = data;
            }).finally(() => {
                this.progress_list = false;
            });
        },
        encodeURL: function (uri) {
            if (uri === "") uri = this.url;
            if (this.search !== '') uri = this.Cts.fillUrlParameters(uri, 'search', this.search);
            if (this.quantity !== 10) uri = this.Cts.fillUrlParameters(uri, 'paginate', this.quantity);
            if (this.sort.length > 0 && this.sort_direction.length > 0) {
                uri = this.Cts.fillUrlParameters(uri, 'sort', this.sort);
                uri = this.Cts.fillUrlParameters(uri, 'direction', this.sort_direction);
            }
            return uri;
        },
        setSelection: function (item) {
            if(!this.selectable) return;
            item.selected = !item.selected;
            if(!this.multiple){
                this.pagination.data = this.pagination.data.map((i)=>{
                    if(i.id !== item.id) i.selected = false;
                    return i;
                });
                this.selected = item.selected ? [item]: [];
                return;
            }
            if(item.selected) this.selected.push(item);
            else this.selected = this.selected.filter((i)=> i.id !== item.id );
        },
        showDetails: function (item) {
            this.$emit('onShowDetails', item);
        }
    }
}
</script>

<style scoped>
.list-head {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    flex-wrap: wrap-reverse;
    margin-bottom: 1em;
}

.file-card-background {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-evenly
}

.file-card-container {
    background-color: #ffffff;
    background-image: repeating-linear-gradient(45deg, #f1f1f1 25%, transparent 25%, transparent 75%, #f1f1f1 75%, #f1f1f1), repeating-linear-gradient(45deg, #f1f1f1 25%, #fff 25%, #fff 75%, #f1f1f1 75%, #f1f1f1);
    background-position: 0 0, 10px 10px;
    background-size: 20px 20px;
}

.file-card {
    width: 200px;
    height: 100px;
    background-repeat: no-repeat;
    background-position: center center;
    background-size: contain;
    cursor: pointer;
}

.file-active {
    border: 1px solid blue;
}

.file-item{
    background: #5a5c69;
    color: #fff;
    padding:5px
}

.file-item p{
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0px;
}

@media (max-width: 768px) {
    .file-card {
        width: 150px;
        height: 150px;
    }
    .file-item p{
        max-width: 140px;
    }
}
</style>
