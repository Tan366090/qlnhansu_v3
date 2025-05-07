export const cacheMiddleware = (duration) => {
    return (req, res, next) => {
        res.set("Cache-Control", `public, max-age=${duration}`);
        next();
    };
};
