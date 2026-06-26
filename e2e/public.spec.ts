import { test, expect } from "@playwright/test";

const VERDICTS = ["Great Deal", "Fair", "Overpriced", "Avoid"];

test.describe("public catalog", () => {
  test("lists seeded laptops", async ({ page }) => {
    await page.goto("/");
    await expect(
      page.getByRole("heading", { name: "Katalog Laptop Bekas" }),
    ).toBeVisible();
    await expect(page.getByText("ThinkPad T480")).toBeVisible();
  });

  test("opens a laptop detail with a score breakdown", async ({ page }) => {
    await page.goto("/");
    // The full-card link sits behind the card content, so a center click hits
    // the overlay. Click the exposed top-left corner where only the link paints.
    await page
      .getByRole("link", { name: /ThinkPad T480/ })
      .first()
      .click({ position: { x: 4, y: 4 } });
    await expect(page).toHaveURL(/\/laptops\/\d+/);
    // ScoreBreakdown renders "<total>/100"
    await expect(page.getByText("/100")).toBeVisible();
    await expect(
      page.getByText(new RegExp(VERDICTS.join("|"))).first(),
    ).toBeVisible();
  });

  test("search filter narrows the catalog", async ({ page }) => {
    await page.goto("/");
    await page.getByPlaceholder("model / brand").fill("ThinkPad");
    await page.getByRole("button", { name: "Terapkan" }).click();
    await expect(page.getByText("ThinkPad T480")).toBeVisible();
    await expect(page.getByText("ROG Zephyrus G14")).toHaveCount(0);
  });
});

test.describe("self-service evaluator", () => {
  test("shows a live verdict", async ({ page }) => {
    await page.goto("/evaluate");
    await expect(
      page.getByRole("heading", { name: "Cek Laptop Bekas" }),
    ).toBeVisible();
    await expect(page.getByText("/100")).toBeVisible();
    await expect(
      page.getByText(new RegExp(VERDICTS.join("|"))).first(),
    ).toBeVisible();
  });
});

test.describe("theme toggle", () => {
  test("switches to dark mode and persists across reload", async ({ page }) => {
    await page.goto("/");
    const html = page.locator("html");
    // Retry the click until it lands post-hydration (handler attaches on mount).
    // Idempotent: only click while not yet dark, so repeats don't toggle back.
    await expect(async () => {
      if ((await html.getAttribute("data-theme")) !== "dark") {
        await page.getByRole("button", { name: /mode gelap/i }).click();
      }
      expect(await html.getAttribute("data-theme")).toBe("dark");
    }).toPass({ timeout: 10_000 });

    await page.reload();
    await expect(html).toHaveAttribute("data-theme", "dark");
  });
});
