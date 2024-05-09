/** MODELS */
import Field from '@/models/ObjectModels/Field'
import ObjectType from '@/models/ObjectModels/ObjectType'
import MediaImage from '@/models/ObjectModels/MediaImage'
import ObjectTypeRelation from '@/models/ObjectModels/ObjectTypeRelation'

/** TYPES */
import type {TpTheObject} from '@/types/ObjectTypes/TpTheObject'
import type {TpField} from '@/types/ObjectTypes/TpField'
import type {TpMediaImage} from '@/types/ObjectTypes/TpMediaImage'


export default class TheObject {
    public id: number = 0;
    public name: string = "";
    public object_type: ObjectType = new ObjectType();
    public description = "";
    public images: Array<MediaImage> = [];
    public created_at: Date = new Date();

    public custom_fields: Array<Field | ObjectTypeRelation> = [];
    public has_custom_fields: boolean = false;

    constructor() {
    }

    static FromJSON(thObj: TpTheObject) {
        const the_object = new TheObject();
        the_object.id = thObj.id;
        the_object.name = thObj.name;
        the_object.object_type = ObjectType.FromJSON(thObj.object_type);
        the_object.description = thObj.description;
        the_object.created_at = thObj.created_at;
        the_object.custom_fields = thObj.custom_fields.map((fld: TpField) => Field.FromJSON(fld));
        the_object.has_custom_fields = thObj.has_custom_fields;
        // eslint-disable-next-line no-prototype-builtins
        if (thObj.hasOwnProperty('images') && thObj.images != null) {
            the_object.images = thObj.images.map((img: TpMediaImage) => MediaImage.FromJSON(img));
        }

        return the_object;
    }

    getValueFromSlug(slug: string): Field | ObjectTypeRelation | null {
        let response: Field | ObjectTypeRelation | null = null;
        response = this.getFieldRelation(this.custom_fields, slug);
        return response;
    }

    private getFieldRelation(all_fields: Array<Field | ObjectTypeRelation>, slug: string): Field | ObjectTypeRelation | null {
        let response: Field | ObjectTypeRelation | null = null;
        if (all_fields == undefined) return null;
        for (let i: number = 0; i < all_fields.length; i++) {
            const current: Field | ObjectTypeRelation = all_fields[i];
            if (current.status == 'relation' && current.slug == slug) {
                response = current;
                break;
            } else {
                const currentField: Field = current as Field;
                if (currentField.layout == 'field' && currentField.slug == slug) {
                    response = currentField;
                    break;
                } else {
                    response = this.getFieldRelation(currentField.fields, slug);
                    if (response?.slug == slug) break;
                }
            }
        }
        return response;
    }
}
