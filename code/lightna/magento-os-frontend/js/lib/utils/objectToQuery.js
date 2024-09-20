export function objectToQuery(data, prefix) {
    let q = [],
        p;
    for (p in data) {
        if (data.hasOwnProperty(p)) {
            const k = prefix ? prefix + '[' + p + ']' : p;
            const v = data[p];
            q.push(
                v !== null && typeof v === 'object'
                    ? objectToQuery(v, k)
                    : encodeURIComponent(k) + '=' + encodeURIComponent(v),
            );
        }
    }
    return q.join('&');
}
