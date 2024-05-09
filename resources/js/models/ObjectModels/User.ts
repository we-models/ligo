/** MODELS */
import TheObject from "@/models/ObjectModels/TheObject";
import MediaImage from "@/models/ObjectModels/MediaImage";
/** TYPES */
import type {TpUser} from '@/types/ObjectTypes/TpUser'
import type {TpMediaImage} from "@/types/ObjectTypes/TpMediaImage";



export default class User {
    public id: number | null = 0;
    public enable: boolean = true;
    public name: string = "";
    public lastname: string = "";
    public document_type: TheObject = new TheObject();
    public ndocument: string = "";
    public birthday: Date = new Date();
    public ncontact: string = "";
    public area: TheObject = new TheObject();
    public email: string = "";
    public code: string = "";
    public created_at: Date = new Date();
    public images: Array<TpMediaImage> = [];

    constructor() {

    }

    static FromJSON(user: TpUser) {
        const currentUser = new User();
        currentUser.id = user.id;
        currentUser.enable = user.enable == 1;
        currentUser.name = user.name;
        currentUser.lastname = user.lastname;
        currentUser.document_type = TheObject.FromJSON(user.document_type);
        currentUser.ndocument = user.ndocument;
        currentUser.email = user.email;
        currentUser.code = user.code;
        currentUser.created_at = user.created_at;

        if (user.hasOwnProperty('images')) {
            currentUser.images = user.images.map((img: TpMediaImage) => MediaImage.FromJSON(img));
        }
        return currentUser;
    }
}
