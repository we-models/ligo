/** TYPES */
import type {TpMediaImage} from '@/types/ObjectTypes/TpMediaImage'

export default class MediaImage {
    public id: number = 0;
    public name: string = "";
    public size: number = 0;
    public height: number = 0;
    public width: number = 0;
    public extension: string = "";
    public mimetype: string = "";
    public user: number = 0;
    public url: string = "";
    public permalink: string = "";
    public created_at: Date = new Date();


    constructor() {
    }

    static FromJSON(mdI: TpMediaImage) {
        const media_image = new MediaImage();
        media_image.id = mdI.id;
        media_image.name = mdI.name;
        media_image.extension = mdI.extension;
        media_image.mimetype = mdI.mimetype;
        media_image.created_at = mdI.created_at;
        media_image.height = mdI.height;
        media_image.width = mdI.width;
        media_image.url = mdI.url;
        media_image.permalink = mdI.permalink;
        return media_image;
    }
}
