<template>
    <div>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane"
                    type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
                    {{ $t('Code') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane"
                    type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false"
                    v-on:click="reformatJson()">
                    {{ $t('Prettify') }}
                </button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab"
                tabindex="0">
                <div style="padding:1em">
                    <textarea class="form-control" :name="theKey" id="" cols="30" rows="10"
                        v-model="string_value">{{ string_value }}</textarea>
                </div>
            </div>
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                <div style="padding:1em">
                    <pre v-html="syntaxHighlight()"></pre>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['ent', 'theKey'],
    name: "ArrayJsonComponent",
    data() {
        return {
            string_value: this.ent
        }
    },
    methods: {
        syntaxHighlight: function () {
            try {
                let json = JSON.parse(this.string_value);
                json = JSON.stringify(json, undefined, 4);
                json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                    var cls = 'number';
                    if (/^"/.test(match)) {
                        if (/:$/.test(match)) {
                            cls = 'key';
                        } else {
                            cls = 'string';
                        }
                    } else if (/true|false/.test(match)) {
                        cls = 'boolean';
                    } else if (/null/.test(match)) {
                        cls = 'null';
                    }
                    return '<span class="' + cls + '">' + match + '</span>';
                });
            } catch (e) {

            }
        },
        reformatJson: function () {
            try {
                let json = JSON.parse(this.string_value);
                json = JSON.stringify(json, undefined, 4);
                this.string_value = json;
            } catch (e) {

            }
        }
    }
}
</script>
