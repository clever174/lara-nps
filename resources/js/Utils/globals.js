
export const GETCOURSE_URL = 'https://tsss.site'

export function getQueryParams(url) {
    const u = new URL(url, window.location.origin)
    return Object.fromEntries(u.searchParams.entries())
}

export function cleanQuery(obj) {
    return Object.fromEntries(
        Object.entries(obj).filter(([_, v]) =>
            v !== null && v !== undefined && v !== ''
        )
    )
}
