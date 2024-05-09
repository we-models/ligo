<template>
    <div>
        <ul class="nav nav-tabs" :id="this.field_id" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    :id="`${this.field_id}-editor-tab`"
                    data-bs-toggle="tab"
                    :data-bs-target="`#${this.field_id}-editor-pane`"
                    type="button"
                    role="tab"
                    :aria-controls="`${this.field_id}-editor-pane`"
                    aria-selected="true">
                    {{ $t('Editor') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    :id="`${this.field_id}-html-tab`"
                    data-bs-toggle="tab"
                    :data-bs-target="`#${this.field_id}-html-pane`"
                    type="button"
                    role="tab"
                    :aria-controls="`${this.field_id}-html-pane`"
                    aria-selected="false">
                    {{ $t('HTML Code') }}
                </button>
            </li>
        </ul>
        <div class="tab-content" :id="`${this.field_id}_tab_content`">
            <div class="tab-pane fade show active" :id="`${this.field_id}-editor-pane`" role="tabpanel" :aria-labelledby="`${this.field_id}-editor-tab`" tabindex="0">
                <div style="padding:1em">
                    <div :qill_name="theKey" class="editor_text" v-html="entity ?? ''"> </div>
                </div>
            </div>
            <div class="tab-pane fade" :id="`${this.field_id}-html-pane`" role="tabpanel" :aria-labelledby="`${this.field_id}-html-tab`" tabindex="0">
                <div style="padding: 1em">
                    <textarea :name="theKey" rows= 10 class="form-control" v-model="entity"> </textarea>
                </div>
            </div>
        </div>

    </div>
</template>

<script>

import { v4 as uuidv4 } from 'uuid';
export default {
    name: "TextAreaComponent",
    props: ['ent', 'toolbar', 'theKey', 'placeholder'],
    data() {
        return {
            entity: this.ent,
            field_id :  "a"  + uuidv4().toString(),
            all_fonts : []
        }
    },
    mounted: function () {

        this.all_fonts = window['all_fonts'] == undefined ? [] : window['all_fonts'];
        let editor = this.$el.querySelector('.editor_text');
        let styleFonts = document.createElement('style');
        let styleHtml= "";
        this.all_fonts.forEach((style)=>{
            let style_formated = style.replace(/\s+/g, '-');
            styleHtml+= `span[data-label="${style_formated}"]::before { font-family: "${style}";}
                             span[data-value="${style_formated}"]::before{ content : "${style}" !important; font-family: "${style}";}
                             .ql-font-${style_formated} { font-family: "${style}"; }\n`;
        });
        styleFonts.innerHTML = styleHtml;
        document.head.appendChild(styleFonts);

        this.all_fonts = this.all_fonts.map((font)=>font.replace(/\s+/g, '-'));

        let Font = Quill.import('formats/font');
        Font.whitelist = this.all_fonts;

        Quill.register(Font, true);

        let custom_toolbar = [];
        let colors = window['all_colors'];
        custom_toolbar.push([{ color: colors }, { background: colors }])
        custom_toolbar.push([{font: this.all_fonts}]);
        custom_toolbar = custom_toolbar.concat(this.toolbar);

        new Quill(editor, {
            theme: 'snow',
            modules: {
                toolbar: {container: custom_toolbar},
                history: {
                    delay: 2000,
                    maxStack: 500,
                    userOnly: true
                },
                imageResize: {
                    modules: ['Resize', 'DisplaySize', 'Toolbar']
                },
            },
            placeholder: this.placeholder
        }).on('editor-change', function (eventName, other) {
            let field = editor.getAttribute('qill_name');
            jQuery("textarea[name=".concat(field, "]")).val(editor.querySelector('.ql-editor').innerHTML);
        });
    }

}

</script>


<style scope>
    .ql-snow .ql-color-picker .ql-picker-options{
        width: 340px;
    }
</style>

