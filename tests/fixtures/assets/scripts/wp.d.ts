/* eslint-disable @typescript-eslint/no-unused-vars */
import type AWN from 'awesome-notifications';
import type { AwnLabelOptions, AwnMessageOptions } from 'awesome-notifications';
// import * as Backboned from 'backbone';

declare global {
  const Backbone: typeof Backbone;
  const _: _.UnderscoreStatic;

  interface Window {
    ajaxurl: string;

    log_id: number;

    wss: {
      api: string;
      nonce: string;
    };

    woosync: {
      i18n: {
        awn: {
          awnLabels: AwnLabelOptions;
          awnMessages: AwnMessageOptions;
        };
      };
    };

    dynamicMarkupData: {
      min: number;
      margin: string | number;
    }[];

    editMap: boolean;

    awn: AWN;

    wp: {
      template: (id: string) => _.CompiledTemplate;
    };
  }

  interface SyncProgress {
    percent: number;
    batch_text: string;
    items_text: string;
    data: Record<string, SyncProgressData>;
    labels: string;
    status: string;
    date: string;
  }

  interface SyncProgressData {
    count: number;
    percent: number;
    text: string;
  }

  interface Select2Response {
    val: string | number;
    text: string | number;
  }

  interface SupplierResponse {
    success: boolean;
    expires: number;
    data: SupplierCategory[];
  }

  interface SupplierCategory {
    group1?: string;
    group2?: string;
    group3?: string;
  }

  interface GroupData {
    name: string;
    active: boolean;
    value: string;
  }
}

export default global;
