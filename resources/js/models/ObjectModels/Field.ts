/** MODELS */
import TheType from '@/models/ObjectModels/TheType'
import TheObject from '@/models/ObjectModels/TheObject'
import MediaImage from '@/models/ObjectModels/MediaImage'
import MediaFile from '@/models/ObjectModels/MediaFile'
import ObjectTypeRelation from '@/models/ObjectModels/ObjectTypeRelation'

/** TYPES */
import type {TpField} from '@/types/ObjectTypes/TpField'


export default class Field {
    public id: number = 0;
    public name: string = "";
    public slug: string = "";
    public object_type: number = 0;
    public layout: string = "field";
    public description: string = "";
    public type: TheType = new TheType();
    public enable: boolean = true;
    public required: boolean = true;
    public editable: boolean = true;
    public tab: number = 0;
    public format: string = "collapse";
    public show_tab_name: boolean = true;
    public order: number = 0;
    public width: number = 0;
    public default: any = null;
    public accept: string = "";
    public created_at: Date = new Date();


    public status: string = "field";
    public value: string = "";
    public entity: any | null = null;
    public fields: Array<Field | ObjectTypeRelation> = [];


    constructor() {
    }

    static FromJSON(fld: TpField): Field {
        const field = new Field();
        field.id = fld.id;
        field.name = fld.name;
        field.slug = fld.slug;
        field.object_type = fld.object_type;
        field.layout = fld.layout;
        field.description = fld.description;
        field.type = TheType.FromJSON(fld.type);
        field.enable = fld.enable == 1;
        field.required = fld.required == 1;
        field.editable = fld.editable == 1;
        field.tab = fld.tab;
        field.format = fld.format;
        field.show_tab_name = fld.show_tab_name == 1;
        field.order = fld.order;
        field.width = fld.width;
        field.default = fld.default;
        field.accept = fld.accept;
        field.created_at = fld.created_at;

        field.status = fld.status;
        field.value = fld.value;
        field.entity = Field.getEntity(fld.entity, field.slug, field.type);
        field.fields = fld.fields == undefined ? [] : fld.fields.map((f) => {
            if (f.relation != undefined) return ObjectTypeRelation.FromJSON(f);
            return Field.FromJSON(f);
        });

        return field;
    }

    static getEntity(entity: any, slug: string, type: TheType): MediaImage | MediaFile | TheObject | null {
        if (entity == null) return null;
        const ent = entity[slug];
        if (ent == null) return null
        if (type.name == 'Image') return MediaImage.FromJSON(ent);
        if (type.name == 'File') return MediaFile.FromJSON(ent);
        return TheObject.FromJSON(ent)
    }

}
