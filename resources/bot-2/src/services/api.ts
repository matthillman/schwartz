import { inject, injectable } from 'inversify';
import { AccessToken, ClientCredentials } from 'simple-oauth2';
import got, { Got, HTTPAlias, Options, OptionsOfJSONResponseBody } from 'got';
import { TYPES } from '../ioc/types';

export interface ClientConfig {
    id: string;
    secret: string;
    scope: string;
};

@injectable()
export class API {
    private tokenClient: ClientCredentials;
    private scope: string;

    private api: Got;

    constructor(
        @inject(TYPES.ApiClient) clientConfig: ClientConfig,
        @inject(TYPES.ApiHost) apiHost: string,
    ) {
        const oauthConfig = {
            client: {
                id: clientConfig.id,
                secret: clientConfig.secret,
            },
            auth: {
                tokenHost: apiHost,
                tokenPath: `/oauth/token`,
            },
        }
        this.tokenClient = new ClientCredentials(oauthConfig);
        this.scope = clientConfig.scope;

        this.api = got.extend({
            prefixUrl: apiHost,
            https: {
                rejectUnauthorized: false,
            },
            headers: {
                authorization: 'Bearer fake',
                accept: 'application/json',
                schwartz: 'bot',
            },
            responseType: 'json',
            hooks: {
                afterResponse: [
                    async (response, retryWithMergedOptions) => {
                        if (response.statusCode === 401) {
                            const newToken = await this.getToken();
                            const updatedOptions: Options = {
                                headers: {
                                    authorization: `${newToken.token.token_type} ${newToken.token.access_token}`,
                                },
                            };

                            this.api.defaults.options = got.mergeOptions(this.api.defaults.options, updatedOptions);

                            return retryWithMergedOptions(updatedOptions);
                        }

                        return response;
                    },
                ],
            },
            mutableDefaults: true,
        });
    }

    async ping() {
        return await this.post(`ping`);
    }

    get(route: string) {
        return this.api.get<any>(`api/${route}`);
    }
    search(route: string) {
        return this.api.get<any>(`${route}`, {prefixUrl: 'https://schwartz.hillman.me/'});
    }
    post(route, data = {}) {
        return this.api.post(`api/${route}`, {json: data});
    }

    execute(method: HTTPAlias, route: string, data = {}) {
        const opts: OptionsOfJSONResponseBody = { method };
        if (method === 'get') {
            opts.searchParams = data;
        }  else {
            opts.json = data;
        }
        return this.api<any>(`api/${route}`, opts);
    }

    get authHeader() {
        return this.api.defaults.options.headers.authorization;
    }
    get baseURL() {
        return this.api.defaults.options.prefixUrl;
    }

    private async getToken() {
        try {
            return await this.tokenClient.getToken({ scope: this.scope }, { rejectUnauthorized: false });
        } catch (err) {
            console.error(`Error getting bearer token: ${err.message}`);
        }

        return { token: {} } as AccessToken;
    }
}
