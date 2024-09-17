export class SyncListRoute {
  private bars: Array<HTMLDivElement>;

  private intervals: number[];

  public init(): void {
    this.intervals = [];
    this.bars = [
      ...document.querySelectorAll<HTMLDivElement>(
        '.woosync-progress-wrapper.inprog',
      ),
    ];
  }

  public finalize(): void {
    if (this.bars.length === 0) {
      return;
    }

    this.intervals = this.bars.map((bar, index) =>
      window.setInterval(() => this.checkProgress(bar, index), 3000),
    );

    this.bindEvents();
  }

  private bindEvents(): void {
    document
      .querySelectorAll<HTMLAnchorElement>('td.ID a.disabled')
      .forEach((anchor) =>
        anchor.addEventListener('click', (e) => this.stopUnconvertedView(e)),
      );
  }

  private stopUnconvertedView(e: Event): void {
    e.preventDefault();
    alert(
      'This synchronization is either not yet finished, or the log file has not been converted. Please wait...',
    );
  }

  private checkProgress(bar: HTMLDivElement, index: number): void {
    const syncId = bar.dataset.id;
    fetch(
      `${window.ajaxurl}?action=woosync_get_sync_progress&sync_id=${syncId}`,
      {
        method: 'GET',
      },
    )
      .then((response) => response.json())
      .then((progress) => this.updateProgress(bar, progress, index));
  }

  private updateProgress(
    bar: HTMLDivElement,
    response: SyncProgress,
    index: number,
  ): void {
    const batchInfo = bar.querySelector<HTMLDivElement>('.batch-info');
    const itemsInfo = bar.querySelector<HTMLDivElement>('.items-info');
    const progress = bar.querySelector<HTMLDivElement>('.woosync-progress-bar');
    const pbHolder = progress.parentElement;
    const labelWrap = bar.querySelector<HTMLSpanElement>(
      '.woosync-progress-label',
    );
    const syncRow = bar.closest('tr');
    const dateCell = syncRow.querySelector('td.column-date');
    const statusCell = syncRow.querySelector('td.column-status');

    labelWrap.innerHTML = response.labels;

    // Iterate over data object
    if (progress) {
      for (const key in response.data) {
        const pct = response.data[key].percent;
        let bar = progress.querySelector<HTMLSpanElement>(`.bar.${key}`);

        if (pct === 0) {
          bar?.remove();
        }

        if (!bar && pct > 0) {
          bar = document.createElement('span');
          bar.classList.add('bar', key);
          progress.appendChild(bar);
        }

        if (pct > 0) {
          bar.style.width = `${pct}%`;
        }
      }
      batchInfo.innerHTML = response.batch_text;
      itemsInfo.innerHTML = response.items_text;
    }

    dateCell.innerHTML = response.date;
    statusCell.innerHTML = response.status;

    if (response.percent >= 100) {
      batchInfo.remove();
      pbHolder.remove();
      window.clearInterval(this.intervals[index]);
    }
  }
}
