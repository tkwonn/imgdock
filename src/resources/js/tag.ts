import { GridLayoutHandler} from "./components/grid";

document.addEventListener('DOMContentLoaded', (): void => {
    const container = document.getElementById('tagContainer') as HTMLElement;
    const rawTagId: string | undefined = container?.dataset.tagId;
    const tagId: string | undefined = rawTagId ? rawTagId : undefined;
    new GridLayoutHandler({ tagId });
});