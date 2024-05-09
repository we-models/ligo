/** MODELS */
import MediaImage from '@/models/ObjectModels/MediaImage'

/** TYPES */
import type {TpMediaFile} from '@/types/ObjectTypes/TpMediaFile'
import type {TpMediaImage} from '@/types/ObjectTypes/TpMediaImage'

export default class MediaFile {
    public id: number = 0;
    public name: string = "";
    public size: number = 0;
    public extension: string = "";
    public mimetype: string = "";
    public user: number = 0;
    public url: string = "";
    public permalink: string = "";
    public created_at: Date = new Date();

    public images: Array<MediaImage> = [];

    constructor() {
    }

    static FromJSON(mdFile: TpMediaFile) {
        const media_file = new MediaFile();
        media_file.id = mdFile.id;
        media_file.name = mdFile.name;
        media_file.size = mdFile.size;
        media_file.extension = mdFile.extension;
        media_file.mimetype = mdFile.mimetype;
        media_file.user = mdFile.user;
        media_file.url = mdFile.url;
        media_file.permalink = mdFile.permalink;
        media_file.created_at = mdFile.created_at;
        media_file.images = mdFile.images.map((mdF: TpMediaImage) => MediaImage.FromJSON(mdF));
        return media_file;
    }
}
