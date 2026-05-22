import { DefaultResponse } from "../DefaultResponse";
import { User } from "./User";
import { UserRole } from "./UserRole";

export class LoginResponse extends DefaultResponse {
    token!: string;
    data!: {
        token?: string;
        expires_at?: Date;
        user?: User;
        roles?: UserRole[];
    }
}