import type { WordPackConfig } from "@x-wp/wordpack";

const config: Partial<WordPackConfig> = {
  fontname: "[name].[contenthash:8]",
  bundles: [
    {
      name: "admin",
      files: ["./scripts/admin/woosync.ts", "./styles/admin/woosync.scss"],
    },
  ],
  paths: {
    scripts: { src: "scripts", dist: "js" },
    styles: { src: "styles", dist: "css" },
  },
};

export default config;
