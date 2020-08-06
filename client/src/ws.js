import * as buildQuery from "http-build-query";

/**
 * @param channel
 * @param filter
 */
export function createWs(channel) {
    return new WebSocket(
        (process.env.REACT_APP_WS_BASE_PATH  || "wss://ws.bot.grutenko.ru") + "?" + buildQuery({channel})
    );
}