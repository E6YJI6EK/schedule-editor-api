import type { ApiSuccess } from "@/core/fetch-client/types";
import { http } from '@/core/fetch-client/http'
import type { Group } from "./types";

export async function searchGroupsByName(params: {
  name?: string;
}): Promise<ApiSuccess<Group[]>> {
  const res = await http.get<ApiSuccess<Group[]>>("/groups/search-by-name", { params });
  return res.data;
}
