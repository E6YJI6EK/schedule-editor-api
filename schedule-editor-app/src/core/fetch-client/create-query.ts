import { onMounted, ref, type Ref } from "vue";
import { http } from "./http";
import type { ApiSuccess, ApiError } from "./types";

type QueryParams = {
    url: string;
    params?: Record<string, string | number | boolean | undefined>;
}

type QueryResult<T> = {
    data: Ref<T | null>;
    isLoading: Ref<boolean>;
    isError: Ref<boolean>;
    isSuccess: Ref<boolean>;
    refetch: () => Promise<void>;
};

export const createQuery = <T>(
    options: QueryParams
): QueryResult<T> => {
    const { url, params } = options;

    const data = ref<T | null>(null);
    const isLoading = ref(false);
    const isError = ref(false);
    const isSuccess = ref(false);

    let requestId = 0;

    const fetch = async (): Promise<void> => {
        const id = ++requestId;

        try {
            isLoading.value = true;
            isError.value = false;
            isSuccess.value = false;

            const res = await http.get<ApiSuccess<T> | ApiError>(url, { params });

            if (id !== requestId) return;

            if (res.data.success) {
                data.value = res.data.data;
                isSuccess.value = true;
            } else {
                isError.value = true;
            }
        } catch {
            if (id !== requestId) return;
            isError.value = true;
        } finally {
            if (id === requestId) {
                isLoading.value = false;
            }
        }
    };

    onMounted(fetch);

    return {
        // @ts-ignore
        data,
        isLoading,
        isError,
        isSuccess,
        refetch: fetch,
    };
};
