/** TYPES */
import type {TpTheType} from '@/types/ObjectTypes/TpTheType'

export default class TheType {
    public id: number = 0;
    public name: string = "String";

    constructor() {
    }

    public static FromJSON(tTp: TpTheType): TheType {
        let the_type = new TheType();
        the_type.id = tTp.id;
        the_type.name = tTp.name;
        return the_type;
    }
}
