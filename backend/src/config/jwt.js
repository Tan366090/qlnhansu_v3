import jwt from "jsonwebtoken";
import config from "./config.js";

class JWT {
    static sign(payload) {
        return jwt.sign(payload, config.jwt.secret, {
            expiresIn: config.jwt.expiresIn
        });
    }

    static verify(token) {
        try {
            return jwt.verify(token, config.jwt.secret);
        } catch (error) {
            throw new Error("Invalid token");
        }
    }

    static decode(token) {
        try {
            return jwt.decode(token);
        } catch (error) {
            throw new Error("Invalid token");
        }
    }
}

export default JWT; 