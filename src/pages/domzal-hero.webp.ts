import type { APIRoute } from "astro";
import { heroImageBase64 } from "../data/heroImage";

export const prerender = true;

export const GET: APIRoute = () => {
  const binary = atob(heroImageBase64);
  const bytes = new Uint8Array(binary.length);

  for (let index = 0; index < binary.length; index += 1) {
    bytes[index] = binary.charCodeAt(index);
  }

  return new Response(bytes, {
    headers: {
      "Content-Type": "image/webp",
      "Cache-Control": "public, max-age=31536000, immutable"
    }
  });
};
